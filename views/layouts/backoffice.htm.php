<?php
/**
 * @var App\Core\App $app
 * @var string $section
 * @var string $page
 * @var string $content
 */
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta name="robots" content="noindex">
    <link rel="manifest" href="<?= url() ?>manifest.json">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Example</title>
    <link rel="stylesheet" href="<?= resource('css', 'style.css', true) ?>">
    <script src="<?= resource('js', 'dist/common.js', true); ?>"></script>
    <script>var baseurl = '<?= url() ?>';</script>
    <script src="<?= url() ?>node_modules/js-polyfills/polyfill.min.js"></script>
</head>

<body>

<header class="card">
    <div class="logo-holder">
        <a href="<?= url(); ?>" class="href">
            <!-- logo -->
        </a>
    </div>
    <nav class="navbar-top">
        <ul>
            <li>Example</li>
            <li>Example</li>
        </ul>
    </nav>
</header>

<div class="master">
    <div class="content">
        <div class="page" id="page"><?= $content; ?></div>
    </div>
</div>

<script src="<?= resource('js', 'dist/main.js', true) ?>"></script>
</body>
</html>