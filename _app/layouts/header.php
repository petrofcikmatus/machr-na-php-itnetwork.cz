<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= isset($title) ? $title : "Title" ?></title>
    <link rel="stylesheet" href="<?= url("assets/css/app.css") ?>">
</head>
<body>

<?php if (has_messages()) : ?>
    <div class="messages">
        <?php foreach (get_messages() as $message) : ?>
            <div class="message"><?= plain($message) ?></div>
        <?php endforeach ?>
    </div>
<?php endif ?>
