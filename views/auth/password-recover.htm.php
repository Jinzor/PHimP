<?php
/**
 * @var string $error
 * @var string $email
 */
?>
<div class="wrapper scrollable-content">
    <form class="login-form" action="<?= route('auth.password-recover-request') ?>" method="post">
        <h1 class="title size-4"><?= _("password-recover-title") ?></h1>
        <p><?= _("password-recover-text") ?></p>
        <div class="field">
            <label><?= _("email") ?></label>
            <input class="input" type="text" value="<?= $email ?>" disabled/>
        </div>
        <div class="field">
            <input type="submit" class="btn btn-l bg" value="<?= _("password-recover-button") ?>"/>
        </div>
        <div class="error-message"><?= $error ?></div>
    </form>
</div>