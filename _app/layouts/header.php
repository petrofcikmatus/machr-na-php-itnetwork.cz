<?php
$am = new AuthModel();
$is_logged_in = $am->isLoggedIn();
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= isset($title) ? $title : "Title" ?></title>
    <link rel="stylesheet" href="<?= url("assets/css/bootstrap.min.css") ?>">
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
                <li><a href="#">Link 1</a></li>
                <li><a href="#">Link 2</a></li>
                <li><a href="#">Link 3</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <?php if ($is_logged_in) { ?>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Môj účet <span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="<?= url("detail-uctu") ?>">Detail účtu</a></li>
                            <li><a href="<?= url("zmena-emailu") ?>">Zmena emailu</a></li>
                            <li><a href="<?= url("zmena-hesla") ?>">Zmena hesla</a></li>
                        </ul>
                    </li>
                    <li><a href="<?= url("odhlasenie") ?>">Odhlásenie</a></li>
                <?php } else { ?>
                    <li><a href="<?= url("prihlasenie") ?>">Prihlásenie</a></li>
                    <li><a href="<?= url("registracia") ?>">Registrácia</a></li>
                    <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Ostatné <span class="caret"></span></a>
                        <ul class="dropdown-menu" role="menu">
                            <li><a href="<?= url("aktivacia-uctu") ?>">Aktivácia účtu</a></li>
                            <li><a href="<?= url("obnova-hesla") ?>">Obnova hesla</a></li>
                        </ul>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </div>
</nav>

<?php if (has_messages()) { ?>
    <div class="container">
        <?php foreach (get_messages() as $message) { ?>
            <div class="alert alert-dismissible alert-info">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <?= plain($message) ?>
            </div>
        <?php } ?>
    </div>
<?php } ?>
