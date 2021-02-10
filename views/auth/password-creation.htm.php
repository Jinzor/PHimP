<?php
/**
 * @var User $user
 * @var string $token
 * @var string $error
 */

use App\Models\User; ?>
<div class="wrapper scrollable-content">
    <form class="login-form" action="<?= route('auth.password-post') ?>" method="post">
        <h1 class="title size-4"><?= _("generatepassword-title") ?></h1>
        <?php if ($user->last == 0 && $user->password == null): ?>
            <p><?= _("generatepassword-first-connection") ?></p>
        <?php endif; ?>
        <input type="hidden" name="token" value="<?= $token ?>">
        <input type="hidden" name="user_id" value="<?= $user->id ?>">
        <div class="field dir-vertical">
            <label><?= _("generatepassword-email") ?></label>
            <input class="input" type="text" value="<?= $user->email ?>" disabled/>
        </div>
        <div class="field dir-vertical">
            <label><?= _("generatepassword-password") ?></label>
            <input class="input" type="password" name="password" value="" autocomplete="new-password" required/>
        </div>
        <div class="field dir-vertical">
            <label><?= _("generatepassword-password-confirm") ?></label>
            <input class="input" type="password" name="password_confirm" value="" autocomplete="off" required/>
        </div>
        <div class="field dir-vertical">
            <input type="submit" class="btn bg" value="<?= _("generatepassword-btn") ?>"/>
        </div>
        <div class="error-message"><?= $error ?></div>
    </form>
</div>