<?php

namespace App\Core;

use App\Models\Batiment;
use App\Models\Departement;
use App\Models\Lycee;
use App\Models\Phase;
use App\Models\Prescription;

/**
 * Class App
 *
 * @author Loïc Brisset
 */
class App
{
    private static $_instance;
    private static $_elastic;

    protected $auth;

    private $departement;
    private $lycee;
    private $batiment;
    private $erp;
    private $prescription;
    private $phaseConfig;
    private $phase;

    const SESSION_ALERT = 'flash_message';

    /**
     * @return App
     */
    public static function getInstance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new App();
        }
        return self::$_instance;
    }

    /**
     * @return \Elasticsearch\Client
     */
    public static function getElasticClient() {
        if (is_null(self::$_elastic)) {
            self::$_elastic = \Elasticsearch\ClientBuilder::create()
                ->setHosts([ELASTIC_HOST])
                ->build();
            if (!self::$_elastic->ping()) {
                Dbg::error('Erreur lors de la connexion à Elastic search => ' . ELASTIC_HOST);
            }
        }
        return self::$_elastic;
    }


    public function auth() {
        if (is_null($this->auth)) {
            $this->auth = new Auth();
        }
        return $this->auth;
    }

    /**
     * @return Departement
     */
    public function departement() {
        if (!$this->departement) {
            $this->departement = Request::valueRequestOrSession('departement', Departement::class);
        }
        return $this->departement;
    }

    /**
     * @return Lycee
     */
    public function lycee() {
        if (!$this->lycee) {
            $this->lycee = Request::valueRequestOrSession('lycee', Lycee::class);
        }
        return $this->lycee;
    }

    /**
     * @return Batiment
     */
    public function batiment() {
        if (!$this->batiment) {
            $this->batiment = Request::valueRequestOrSession('batiment', Batiment::class);
        }
        return $this->batiment;
    }

    /**
     * @return string
     */
    public function erp() {
        if (!$this->erp) {
            $this->erp = Request::valueRequestOrSession('erp');
        }
        return $this->erp;
    }

    /**
     * @return Prescription
     */
    public function prescription() {
        if (!$this->prescription) {
            $this->prescription = Request::valueRequestOrSession('prescription', Prescription::class);
        }
        return $this->prescription;
    }

    /**
     * Retourne l'objet en cours
     *
     * @return Batiment|Departement|Lycee|null
     */
    public function getCurrentObject() {

        $object = null;
        if (!empty($this->batiment())) {
            $object = $this->batiment;
        } elseif (!empty($this->lycee())) {
            $object = $this->lycee;
        } elseif (!empty($this->departement())) {
            $object = $this->departement;
        }

        return $object;
    }

    public function setAlert($data) {
        if (!is_array($data) || key($data) === 0) {
            $data['message'] = $data;
        }
        $_SESSION[self::SESSION_ALERT] = $data;
    }

    public function getAlert($key = '') {
        if (isset($_SESSION[self::SESSION_ALERT]) && !empty($_SESSION[self::SESSION_ALERT])) {
            $alerts = $_SESSION[self::SESSION_ALERT];
            unset($_SESSION[self::SESSION_ALERT]);
            if (!empty($key) && isset($alerts[$key])) {
                return $alerts[$key];
            }
            return $alerts;
        }
        return null;
    }

    /**
     * @return Phase
     */
    public function getPhase() {
        if (is_null($this->phase)) {
            $nom = Request::valueRequestOrSession('phase', null, true);
            if (!empty($nom)) {
                $this->phase = Phase::getFromNom($nom);
                $_SESSION['phase'] = $this->phase; // TODO autrement qu'en session (pour possibilité de nouvel onglet)
            }
            if (is_null($this->phase)) {
                $this->phase = self::getConfigPhase();
            }
        }
        return $this->phase;
    }

    /**
     * @return Phase
     */
    public function getConfigPhase() {
        if (is_null($this->phaseConfig)) {
            $this->phaseConfig = Phase::getActive();
        }
        return $this->phaseConfig;
    }
}
