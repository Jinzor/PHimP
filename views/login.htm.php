<?php
/**
 * @var string $login Nom d'utilisateur
 * @var string $error Message d'erreur
 */
?>
<div class="wrapper scrollable-content">
    <form class="login-form" action="<?= route('auth.auth') ?>" method="post">
        <h1 class="title size-4">Connexion</h1>
        <div class="field">
            <label>Nom d'utilisateur</label>
            <input class="input" type="text" name="utilisateur" value="<?= $login ?>" required>
        </div>
        <div class="field">
            <label>Mot de passe</label>
            <input class="input" type="password" name="mdp" value="">
        </div>
        <div class="field">
            <label for="remember" class="checkbox">
                <input id="remember" type="checkbox" value="1" name="remember"/>
                Rester connecter
            </label>
        </div>
        <input type="hidden" name="redirect" value="<?= isset($_GET['redirect']) ? $_GET['redirect'] : '' ?>">
        <div class="field">
            <input type="submit" class="btn bg" value="Se connecter"/>
        </div>
        <div class="error-message"><?= $error ?></div>
        <div class="success-message"><?= $message ?></div>
    </form>
</div>