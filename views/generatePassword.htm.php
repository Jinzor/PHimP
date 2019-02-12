<?php
/**
 * @var \App\Models\User $user
 * @var string $token
 * @var string $error
 */
?>
<div class="wrapper scrollable-content">
    <form class="login-form" action="<?= route('auth.createPassword') ?>" method="post">
        <h1 class="title size-4">Choisissez un mot de passe</h1>
        <?php if ($user->last == 0 && $user->mdp == null): ?>
            <p>Ceci est votre première connexion.</p>
        <?php endif; ?>
        <input type="hidden" name="token" value="<?= $token ?>">
        <input type="hidden" name="user_id" value="<?= $user->id ?>">
        <div class="field">
            <label>Votre adresse email</label>
            <input class="input" type="text" value="<?= $user->email ?>" disabled/>
        </div>
        <div class="field">
            <label>Mot de passe</label>
            <input class="input" type="password" name="password" value="" required/>
        </div>
        <div class="field">
            <label>Confirmez le mot de passe</label>
            <input class="input" type="password" name="password_confirm" value="" required/>
        </div>
        <div class="field">
            <input type="submit" class="btn bg" value="Créer le mot de passe"/>
        </div>
        <div class="error-message"><?= $error ?></div>
    </form>
</div>