<?php

/**
 * Class AuthModel
 */
class AuthModel {

    // ------------------------------------------------------------------------
    // Properties.
    // ------------------------------------------------------------------------

    private static $privateKey = "";
    private static $tokenName  = "auth_token";
    private static $classLock  = false;

    // ------------------------------------------------------------------------
    // Public methods.
    // ------------------------------------------------------------------------

    public function __construct() {
        self::$classLock = true;
    }

    public function doLogin($email, $password) {
    }

    public function doLogout() {
    }

    public function doRegistration($email, $password, $password_again, $name = "Unknown user") {
    }

    public function useActivationKey($email, $key) {
    }

    public function sendRecoveryKey($email) {
    }

    public function useRecoveryKey($email, $key, $password, $password) {
    }

    public function updateEmail($email) {
    }

    public function updatePassword($password_old, $password_new, $password_new_again) {
    }

    public function isLoggedIn() {
        if (!$this->hasLoginToken()) return false;

        $token = $this->getLoginToken();
        $query = "SELECT COUNT(*) FROM active_logins WHERE active_login_token = :token";

        if (Database::getInstance()->queryOne($query, array("token" => $token)) == 0) {
            $this->removeLoginToken();
            return false;
        }

        return true;
    }

    public function isFreeEmail($email) {
    }

    public function isValidEmail($email) {
    }

    public function isValidPassword($password) {
    }

    // ------------------------------------------------------------------------
    // Private methods.
    // ------------------------------------------------------------------------

    /**
     * Vráti náhodne vygenerovanú 128-znakovú soľ.
     * Aj keby mali užívatelia rovnaké heslá, tak sa to nezistí.
     * Metóda sa môže meniť aj po nasadení do produkcie.
     * @return string
     */
    private function getNewPasswordSalt() {
        return hash("sha512", mt_rand() . time() . uniqid());
    }

    /**
     * Vráti náhodne vygenerovaný 6-znakový aktivačný kľúč.
     * Metóda sa môže meniť aj po nasadení do produkcie.
     * @return string
     */
    private function getNewActivationKey() {
        return substr(strtoupper(hash("sha1", mt_rand() . time() . uniqid())), 0, 6);
    }

    /**
     * Vráti náhodne vygenerovaný 128-znakový prihlasovací token.
     * Metóda sa môže meniť aj po nasadení do produkcie.
     * @return string
     */
    private function getNewLoginToken() {
        return hash("sha512", mt_rand() . time() . uniqid());
    }

    /**
     * Vráti 128-znakový hash aktivačného kľúča.
     * Metóda sa po nasadení do produkcie nemôže meniť.
     * @param string $key
     * @return string
     */
    private function getActivationKeyHash($key) {
        return hash("sha512", $key);
    }

    /**
     * Vráti 128-znakový hash kombinácie hesla, soli a privátneho kľúča.
     * Metóda sa po nasadení do produkcie nemôže meniť.
     * @param string $password
     * @param string $salt
     * @return string
     */
    private function getPasswordHash($password, $salt) {
        return hash("sha512", $password . $salt . self::$privateKey);
    }

    /**
     * @return bool
     */
    private function hasLoginToken() {
        return isset($_COOKIE[self::$tokenName]);
    }

    /**
     * @param null $default
     * @return null
     */
    private function getLoginToken($default = null) {
        return $this->hasLoginToken() ? $_COOKIE[self::$tokenName] : $default;
    }

    /**
     * @param string $token
     * @return bool
     */
    private function setLoginToken($token) {
        return setcookie(self::$tokenName, $token, strtotime("+30 days"));
    }

    /**
     * @return bool
     */
    private function removeLoginToken() {
        return setcookie(self::$tokenName, null, 1);
    }

    // ------------------------------------------------------------------------
    // Private static methods.
    // ------------------------------------------------------------------------

    /**
     * Nastavuje privátny kľúč.
     * Každá aplikácia si môže nastaviť iný, a rovnaké heslá
     * s rovnakými soľami ale inými privátnymi kľúčami budú
     * mať rôzne hashe.
     * @param string $privateKey
     * @throws Exception
     */
    public static function setPrivateKey($privateKey) {
        if (self::$classLock) {
            throw new Exception("You cannot set private key after creating instance.");
        }
        self::$privateKey = $privateKey;
    }

    /**
     * Nastavuje názov tokenu v $_COOKIE poli.
     * @param string $tokenName
     * @throws Exception
     */
    public static function setTokenName($tokenName) {
        if (self::$classLock) {
            throw new Exception("You cannot set token name after creating instance.");
        }
        self::$tokenName = $tokenName;
    }
}
