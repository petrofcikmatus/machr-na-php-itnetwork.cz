<?php

// zadefinujeme si cestu počiatku našej aplikácie, budú ju používať funkcie includujúce nejaké súbory
define("APP_PATH", dirname(__FILE__));

// zadefinujeme si url adresu tohto projektu, budú ju používať funkcie url() a redirect(), a možno aj niekde v šablóne
define("APP_URL", "http://localhost/machr-na-php-itnetwork.cz");

// pracujeme so session, tak si ho musíme zapnúť
if (!session_id()) @session_start();

// pomocné funkcie
require "_app/includes/functions.php";

// potrebné triedy na prácu s databázou a s autentifikáciou užívateľov
require "_app/classes/Database.php";
require "_app/classes/AuthModel.php";

// nastavenia pripojenia na databázu, nech si každý nastaví podľa seba ;)
$db_credentials = array(
    "host" => "localhost",
    "port" => 3310,
    "name" => "test",
    "user" => "root",
    "pass" => "root"
);

// predanie nastavenia databázy do databázového wrapperu, ľahšie sa nám bude pracovať.
Database::setCredentials($db_credentials);

// pridanie súkromného kľúča pre túto aplikáciu. Každá apka by mala mať iný :)
AuthModel::setPrivateKey("hlkôajsdkf3215dfa&#asdf");

// routy našej aplikácie a ich zodpovedajúci php súbor
$routes = array(
    ""               => "index",
    "prihlasenie"    => "auth/account-login",
    "odhlasenie"     => "auth/account-logout",
    "registracia"    => "auth/account-registration",
    "aktivacia-uctu" => "auth/account-activation",
    "obnova-hesla"   => "auth/account-password-recovery",
    "moj-ucet"       => "auth/account-details"
);

// vyberieme si prvý segment našej url adresy
$page = segment(1);

// ak sa nenachádza medzi našimi routami, zobrazíme chybovú stránku 404
if (!array_key_exists($page, $routes)) show_404();

// inak zobrazíme konkrétnu stránku v ktorej je ďalšia logika
show_page($routes[$page]);
