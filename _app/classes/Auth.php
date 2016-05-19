<?php

/**
 * Class Auth
 */
class Auth {

    // ------------------------------------------------------------------------
    // Properties.
    // ------------------------------------------------------------------------

    private static $privateKey     = "";
    private static $privateKeyLock = false;

    // ------------------------------------------------------------------------
    // Public methods.
    // ------------------------------------------------------------------------

    public function __construct() {
        self::$privateKeyLock = true;
    }

    public function doLogin($email, $password) {
    }

    public function doLogout() {
    }

    public function doRegistration($email, $password, $password_again, $name = "Unknown user") {
    }

    public function doActivation($email, $key) {
    }

    public function doPasswordReset($email, $key, $password, $password) {
    }

    public function isLoggedIn() {
    }

    public function isFreeEmail($email) {
    }

    public function isValidEmail($email) {
    }

    public function isValidName($name) {
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
    private function getPasswordSalt() {
        return hash("sha512", mt_rand() . time());
    }

    /**
     * Vráti náhodne vygenerovaný 32-znakový aktivačný kľúč.
     * Metóda sa môže meniť aj po nasadení do produkcie.
     * @return string
     */
    private function getActivationKey() {
        return hash("md5", mt_rand() . time());
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
        if (self::$privateKeyLock) throw new Exception("You cannot set private key after creating instance.");
        self::$privateKey = $privateKey;
    }
}
