<?php

$am = new AuthModel();

if ($am->isLoggedIn()) {
    $am->doLogout();
    add_message("Odhlásenie prebelhlo úspešne.");
}

redirect();