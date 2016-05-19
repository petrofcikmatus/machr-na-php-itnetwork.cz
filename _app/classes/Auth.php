<?php

class Auth {

    private function getNewPasswordSalt() {
    }

    private function getPasswordHash($password, $salt = "") {
        return hash("sha256", $password . $salt);
    }

    public function doLogin() {
    }

    public function doLogout() {
    }

    public function doRegistration() {
    }

    public function doActivation() {
    }

    public function doPasswordReset() {
    }
}
