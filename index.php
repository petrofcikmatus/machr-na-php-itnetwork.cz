<?php

// zadefinujeme si cestu počiatku našej aplikácie, budú ju používať funkcie includujúce nejakú súbory
define("APP_PATH", dirname(__FILE__));

// zadefinujeme si url adresu tohto projektu, budú ju používať funkcie url() a redirect(), a možno aj niekde v šablóne
define("APP_URL", "http://localhost/machr-na-php-itnetwork.cz");

// pracujeme so session, tak si ho musíme zapnúť
if (!session_id()) @session_start();

// requirneme si pomocné funkcie
require "_app/includes/functions.php";

// requirneme si potrebné triedy na prácu s databázou a s autentifikáciou užívateľov
require "_app/classes/Database.php";
require "_app/classes/Auth.php";

// nastavenia pripojenia na databázu
$db_credentials = array(
    "host" => "localhost",
    "port" => 3310,
    "name" => "test",
    "user" => "root",
    "pass" => "root"
);

// predanie nastavenia databázy do databázového wrapperu, ľahšie sa nám bude pracovať.
Database::setCredentials($db_credentials);

$routes = array(
    ""               => "index",
    "prihlasenie"    => "auth/account-login",
    "odhlasenie"     => "auth/account-logout",
    "registracia"    => "auth/account-registration",
    "aktivacia-uctu" => "auth/account-activation",
    "obnova-hesla"   => "auth/account-password-recovery"
);

$page = segment(1);

if (!array_key_exists($page, $routes)){
    show_404();
}

show_page($routes[$page]);
