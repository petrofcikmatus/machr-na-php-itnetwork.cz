<?php

$am = new AuthModel();

if ($am->isLoggedIn()) {
    $am->doLogout();
    add_message("Odhlásenie prebehlo úspešne.");
}

redirect();