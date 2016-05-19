<?php

// dump
function d() {
    foreach (func_get_args() as $arg) var_dump($arg);
    //foreach (func_get_args() as $arg) echo "<pre>" . print_r($arg, true) . "</pre>";
}

// dump and die
function dd() {
    foreach (func_get_args() as $arg) d($arg);
    exit;
}

// url
function url($url = "/") {
    return APP_URL . "/" . ltrim($url, "/");
}

// redirect
function redirect($page = "/", $status_code = 301) {
    $page = str_replace(APP_URL, '', $page);
    $page = ltrim($page, '/');

    $location = APP_URL . "/" . $page;

    $codes = array(
        301 => "Moved Permanently",
        302 => "Found",
        303 => "See Other",
        307 => "Temporary Redirect",
    );

    if (!isset($codes[$status_code])) $status_code = 301;

    header("Location: " . $location, true, $status_code);
    header("Connection: close");
    exit;
}

function plain($string) {
    return htmlspecialchars($string, ENT_QUOTES); // potencialne nebezpečné, poradil Luboš Beran
    //return htmlentities($string, ENT_QUOTES);
}

function heal_output($x = null) {
    if (!isset($x)) {
        return null;
    } else if (is_string($x)) {
        return plain($x);
    } else if (is_array($x)) {
        foreach ($x as $k => $v) {
            $x[$k] = heal_output($v);
        }
        return $x;
    }
    return $x;
}

function is_post_request() {
    return (isset($_SERVER["REQUEST_METHOD"]) && strtolower($_SERVER["REQUEST_METHOD"]) === "post");
}

function is_ajax_request() {
    return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
}

// zobrazí stránku
function show_page($view) {
    if (!file_exists($file = APP_PATH . "/_app/pages/" . $view . ".php")) {
        exit("Page file ${file} not found.");
    }

    /** @noinspection PhpIncludeInspection */
    include($file);
}

// zobrazí stránku 404
function show_404() {
    header("HTTP/1.0 404 Not Found");
    if (!file_exists($file = APP_PATH . "/_app/pages/error/404.php")) {
        exit("Page file ${file} not found (and page also not found).");
    }

    /** @noinspection PhpIncludeInspection */
    include $file;
    exit;
}

// pridá layout na stránku
function add_layout($layout, $data = array()) {
    if (!file_exists($file = APP_PATH . "/_app/layouts/" . $layout . ".php")) {
        exit("Layout file ${file} not found.");
    }

    extract(heal_output($data));
    extract($data, EXTR_PREFIX_ALL, "");

    /** @noinspection PhpIncludeInspection */
    include($file);
}


// vráti segmenty v url pre segment() funkciu
function get_segments() {
    // najprv vyskladáme aktuálnu url adresu
    $current_url = "http";
    $current_url .= ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "s://" : "://");
    $current_url .= $_SERVER['HTTP_HOST'];
    $current_url .= $_SERVER['REQUEST_URI'];

    // odstránime z nej nepotrebnú časť url nastavenú v config súbore ako APP_URL
    $path = str_replace(APP_URL, "", $current_url);

    // naparsujeme cestu ktorá nam ostala, a odstránime z nej nepotrebné lomítka na začiatku a na konci
    $path = trim(parse_url($path, PHP_URL_PATH), '/');

    // rozbijeme cestu na segmenty podľa lomítok
    $segments = explode('/', $path);

    // vrátime segmenty
    return $segments;
}

// funkcia vracia segment podľa indexu, alebo false ak segment s takým indexom neexistuje
function segment($index) {
    // získame segmenty
    $segments = get_segments();

    // ak segment s daným indexom existuje, vrátime ho, inak vrátime false
    return isset($segments[$index - 1]) ? $segments[$index - 1] : false;
}

// funkcia ktorá pridá novú správu do session
function add_message($message) {
    if (!has_messages()) $_SESSION["messages"] = array();
    $_SESSION["messages"][] = $message;
}

// funkcia ktorá pridá novú chybovú správu do session
function add_message_error($message) {
    add_message("Chyba aplikácie: " . $message);
}

// funkcia ktorá vyberie správy zo session
function get_messages() {
    if (!has_messages()) return array();

    $messages = $_SESSION["messages"];
    unset($_SESSION["messages"]);
    return $messages;
}

// funkcia zisťuje či máme v session pole pre správy
function has_messages() {
    return (isset($_SESSION["messages"]) && is_array($_SESSION["messages"]));
}
