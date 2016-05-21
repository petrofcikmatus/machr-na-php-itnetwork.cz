<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= isset($title) ? $title : "Title" ?></title>
    <link rel="stylesheet" href="<?= url("assets/css/bootstrap.min.css") ?>">
    <link rel="stylesheet" href="<?= url("assets/css/app.css") ?>">
</head>
<body>

<nav class="navbar navbar-default">
    <div class="container-fluid">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar">
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand" href="<?= url() ?>">Aplikácia</a>
        </div>

        <div class="collapse navbar-collapse" id="navbar">
            <ul class="nav navbar-nav">
                <li<?php if (segment(1) == "prihlasenie") echo " class=\"active\"" ?>><a href="<?= url("prihlasenie") ?>">Prihlásenie</a></li>
                <li<?php if (segment(1) == "registracia") echo " class=\"active\"" ?>><a href="<?= url("registracia") ?>">Registrácia</a></li>
                <li<?php if (segment(1) == "obnova-hesla") echo " class=\"active\"" ?>><a href="<?= url("obnova-hesla") ?>">Obnova hesla</a></li>
                <li<?php if (segment(1) == "aktivacia-uctu") echo " class=\"active\"" ?>><a href="<?= url("aktivacia-uctu") ?>">Aktivácia účtu</a></li>
                <li<?php if (segment(1) == "moj-ucet") echo " class=\"active\"" ?>><a href="<?= url("moj-ucet") ?>">Môj účet</a></li>
                <li<?php if (segment(1) == "odhlasenie") echo " class=\"active\"" ?>><a href="<?= url("odhlasenie") ?>">Odhlásenie</a></li>
            </ul>
        </div>
    </div>
</nav>

<?php if (has_messages()) : ?>
    <div class="container">
        <?php foreach (get_messages() as $message) : ?>
            <div class="alert alert-dismissible alert-info">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?= plain($message) ?>
            </div>
        <?php endforeach ?>
    </div>
<?php endif ?>
