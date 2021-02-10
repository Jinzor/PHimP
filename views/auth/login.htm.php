<?php
/**
 * @var string $login Nom d'utilisateur
 * @var string $error Message d'erreur
 * @var string $message Message
 */
?>
<div class="wrapper scrollable-content">
    <form class="login-form" action="<?= route('auth.auth') ?>" method="post">
        <div class="wrapper-inner">
            <h1 class="title size-4">Connexion</h1>
            <div class="field dir-vertical">
                <label>Email</label>
                <input class="input" type="text" name="username" value="<?= $login ?>" required>
            </div>
            <div class="field dir-vertical">
                <label>Password</label>
                <input class="input" type="password" name="password" value="" autocomplete="off">
            </div>
            <div class="field text-right">
                <label for="remember" class="checkbox">
                    <input id="remember" type="checkbox" value="1" name="remember" checked/>
                    Se souvenir de moi
                </label>
            </div>
            <input type="hidden" name="redirect" value="<?= isset($_GET['redirect']) ? $_GET['redirect'] : '' ?>">
            <div class="field">
                <button type="submit" class="btn btn-m bg">Se connecter</button>
            </div>
            <div class="error-message"><?= $error ?></div>
            <div class="success-message"><?= $message ?></div>
        </div>
    </form>
</div>