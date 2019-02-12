<?php

namespace App\Core;

class Security
{
    const CRYPTO_ALGO = 'aes-256-cbc';
    const CRYPTO_VECTOR_SIZE = 16;
    const CRYPTO_KEY_SIZE = 32;
    const KEY = '1650-Jd7dk*rqS4420:xljFVk789/3$v8'; // TODO change the key

    /**
     * Chiffrage
     *
     * @param string $data
     * @return string
     * @assert ("test") !== false
     */
    public static function crypt(string $data) { // fonction de cryptage
        if (in_array(substr($data, 0, 1), ['!', '*', '#'])) { // déja crypté
            return $data;
        }
        return "#" . self::aesEncryptB64($data, self::KEY);
    }

    /**
     * Déchiffrage standard
     *
     * @param string $data
     * @return string
     * @assert ("test") !== false
     */
    public static function decrypt(string $data) {

        if (substr($data, 0, 1) == '#') {
            return self::aesDecryptB64(substr($data, 1, strlen($data) - 1), self::KEY);
        }

        return $data;
    }

    /**
     * Déchiffrage AES
     *
     * @param string $val
     * @param string $key
     * @param string $vector
     * @return string
     */
    public static function aesDecrypt(string $val, string $key, string $vector = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0") {
        if (strlen($vector) != self::CRYPTO_VECTOR_SIZE) {
            return false;
        }
        $dec = openssl_decrypt($val, self::CRYPTO_ALGO, $key, OPENSSL_RAW_DATA, $vector);
        return rtrim($dec,
            ((ord(substr($dec, strlen($dec) - 1, 1)) >= 0 && ord(substr($dec, strlen($dec) - 1, 1)) <= 16) ? chr(ord(substr($dec,
                strlen($dec) - 1, 1))) : null));
    }

    /**
     *
     * @param string $xval
     * @param string $key
     * @return string
     */
    private static function aesDecryptB64(string $xval, string $key) {
        if (strlen($key) != self::CRYPTO_KEY_SIZE) {
            return false;
        }
        $vector = substr($xval, 0, self::CRYPTO_VECTOR_SIZE);
        $payload = base64_decode(substr($xval, self::CRYPTO_VECTOR_SIZE, strlen($xval) - self::CRYPTO_VECTOR_SIZE));
        return self::aesDecrypt($payload, $key, $vector);
    }

    /**
     * Chiffrage AES
     *
     * @param string $val
     * @param string $key
     * @param string $vector
     * @return string
     */
    public static function aesEncrypt(string $val, string $key, string $vector = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0") {

        if (strlen($vector) != self::CRYPTO_VECTOR_SIZE) {
            return false;
        }
        if (strlen($key) != self::CRYPTO_KEY_SIZE) {
            return false;
        }

        $vald = str_pad($val, (16 * (floor(strlen($val) / 16) + (strlen($val) % 16 == 0 ? 2 : 1))), chr(16 - (strlen($val) % 16)));
        return openssl_encrypt($vald, self::CRYPTO_ALGO, $key, OPENSSL_RAW_DATA, $vector);
    }

    /**
     *
     * @param string $val
     * @param string $key
     * @return string
     */
    private static function aesEncryptB64(string $val, string $key) {
        $vector = self::aesCreateVector();
        $payload = self::aesEncrypt($val, $key, $vector);
        return $vector . base64_encode($payload);
    }

    /**
     *
     * @return string
     * @assert () !== false
     */
    private static function aesCreateVector() {
        return self::randomHexStr(self::CRYPTO_VECTOR_SIZE);
    }

    /**
     *
     * @param int $length
     * @return string
     * @assert () !== false
     */
    public static function randomHexStr(int $length = 40) {
        return self::randomize('0123456789abcdef', $length);
    }

    /**
     *
     * @param int $length
     * @return string
     * @assert () !== false
     */
    public static function randomAlphaNumStr(int $length = 40) {
        $keyspace = 'abcdefghijklmnopqrstuvwxyz'
            . 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
            . '0123456789';
        return self::randomize($keyspace, $length);
    }

    /**
     *
     * @param int $length
     * @return string
     * @assert () !== false
     */
    public static function randomIntStr(int $length = 40) {
        return self::randomize('0123456789', $length);
    }

    private static function randomize($keyspace, $length) {
        $str = '';
        $max = mb_strlen($keyspace, '8bit') - 1;

        for ($i = 0; $i < $length; ++$i) {
            $str .= $keyspace[rand(0, $max)];
        }

        return $str;
    }

    public static function checkPassword($pass1, $pass2) {
        // TODO Vérification avec mot de passe crypté uniquement
        if ($pass1 == $pass2 || Security::decrypt($pass1) == $pass2 || Security::decrypt($pass2) == $pass1) {
            return true;
        }
        return false;
    }

    /**
     * @param int $length
     * @return string
     */
    public static function generateRandomToken($length = 128): string {
        try {
            return bin2hex(random_bytes($length));
        } catch (\Exception $e) {
            Dbg::error($e->getMessage());
        }
        return '';
    }

}
