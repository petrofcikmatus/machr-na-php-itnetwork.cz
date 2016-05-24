<?php

/**
 * Class AuthModel
 */
class AuthModel {

    // ------------------------------------------------------------------------
    // Properties.
    // ------------------------------------------------------------------------

    private static $classLock = false;

    private static $secretPasswordSalt = "";
    private static $secretTokenSalt = "";
    private static $tokenCookieName = "auth_token";

    private $db = null;

    // ------------------------------------------------------------------------
    // Public static methods.
    // ------------------------------------------------------------------------

    /**
     * Nastavuje tajnú soľ pre heslo.
     * Každá aplikácia si môže nastaviť inú, a rovnaké heslá
     * s rovnakými soľami ale inými tajnými soľami budú
     * mať rôzne hashe.
     * @param string $string
     * @throws Exception
     */
    public static function setSecretPasswordSalt($string) {
        if (self::$classLock) {
            throw new Exception("Zmena soli hesla už nie je možná.");
        }
        self::$secretPasswordSalt = $string;
    }

    /**
     * Nastavuje tajnú soľ pre token.
     * Každá aplikácia si môže nastaviť inú, a ukradnuté tokeny
     * aktívnych prihlásení v databázi sa nebudú dať zneužiť.
     * todo: what?!
     * @param string $string
     * @throws Exception
     */
    public static function setSecretTokenSalt($string) {
        if (self::$classLock) {
            throw new Exception("Zmena soli tokenu už nie je možná.");
        }
        self::$secretTokenSalt = $string;
    }

    /**
     * Nastavuje názov tokenu v $_COOKIE poli.
     * @param string $string
     * @throws Exception
     */
    public static function setTokenCookieName($string) {
        if (self::$classLock) {
            throw new Exception("Zmena názvu tokenu už nie je možná.");
        }
        self::$tokenCookieName = $string;
    }

    // ------------------------------------------------------------------------
    // Private methods.
    // ------------------------------------------------------------------------

    /**
     * Vráti true, ak je email valídny, inak false.
     * @param string $email
     * @return mixed
     */
    private function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Vráti true, ak je heslo dostačujúce, inak false.
     * Kontrolujeme len jeho dĺžku, veď nech si užívateľ zvolí čo chce.
     * todo: možno by to chcelo rozšíriť o ďalšie kontroly
     * @param string $password
     * @return bool
     */
    private function isValidPassword($password) {
        return (mb_strlen($password, "utf-8") > 5);
    }

    /**
     * Vráti true, ak je email voľný, inak false.
     * @param string $email
     * @return bool
     */
    private function isFreeEmail($email) {
        if (!$this->isValidEmail($email)) return false;

        $query = "SELECT COUNT(*) FROM users WHERE user_email = :email";
        $param = array("email" => $email);

        if ($this->db->queryOne($query, $param) != 0) return false;
        return true;
    }

    /**
     * Vráti náhodne vygenerovanú 128-znakovú soľ.
     * Aj keby mali užívatelia rovnaké heslá, tak sa to nezistí :)
     * Metóda sa môže meniť aj po nasadení do produkcie.
     * todo: je niečo efektívnejšie pre generovanie takého stringu?
     * @return string
     */
    private function getPasswordSalt() {
        return hash("sha512", mt_rand() . time() . uniqid());
    }

    /**
     * Vráti náhodne vygenerovaný 6-znakový aktivačný kľúč.
     * Metóda sa môže meniť aj po nasadení do produkcie.
     * todo: je niečo efektívnejšie pre generovanie takého stringu?
     * @return string
     */
    private function getActivationKey() {
        return substr(strtoupper(hash("sha1", mt_rand() . time() . uniqid())), 0, 6);
    }

    /**
     * Vráti 128-znakový hash aktivačného kľúča.
     * Metóda sa po nasadení do produkcie nemôže meniť.
     * todo: je niečo efektívnejšie pre generovanie takého stringu?
     * @param string $key
     * @return string
     */
    private function getActivationKeyHash($key) {
        return hash("sha512", $key);
    }

    /**
     * Vráti 128-znakový hash kombinácie hesla, soli a privátneho kľúča.
     * Metóda sa po nasadení do produkcie nemôže meniť.
     * todo: je niečo efektívnejšie pre generovanie takého stringu?
     * @param string $password
     * @param string $salt
     * @return string
     */
    private function getPasswordHash($password, $salt) {
        return hash("sha512", $password . $salt . self::$secretPasswordSalt);
    }

    /**
     * Vráti náhodne vygenerovaný 128-znakový prihlasovací token.
     * Metóda sa môže meniť aj po nasadení do produkcie.
     * todo: je niečo efektívnejšie pre generovanie takého stringu?
     * @return string
     */
    private function getNewLoginToken() {
        return hash("sha512", mt_rand() . time() . uniqid());
    }

    /**
     * Vráti hash prihlasovacieho tokenu.
     * @param string $token
     * @return string
     */
    private function getLoginTokenHash($token) {
        return hash("sha512", $token);
    }

    /**
     * Vráti true, ak existuje prihlasovací token, inak false.
     * @return bool
     */
    private function hasLoginToken() {
        return isset($_COOKIE[self::$tokenCookieName]);
    }

    /**
     * Vráti prihlasovací token.
     * @param null $default
     * @return null
     */
    private function getLoginToken($default = null) {
        return $this->hasLoginToken() ? $_COOKIE[self::$tokenCookieName] : $default;
    }

    /**
     * Pridá cookie s prihlasovacím tokenom.
     * @param string $token
     * @return bool
     */
    private function setLoginToken($token) {
        return setcookie(self::$tokenCookieName, $token, strtotime("+30 days"));
    }

    /**
     * Odstráni cookie s prihlasovacím tokenom.
     * @return bool
     */
    private function removeLoginToken() {
        return setcookie(self::$tokenCookieName, null, 1);
    }

    private function addActiveLogin($user_id, $token_hash) {
        return $this->db->query("INSERT INTO active_logins (active_login_user_id, active_login_token_hash) VALUES (:user_id, :token_hash)", array("user_id" => $user_id, "token_hash" => $token_hash));
    }

    private function removeActiveLogin($token_hash) {
        return $this->db->query("DELETE FROM active_logins WHERE active_login_token_hash = :token_hash", array("token_hash" => $token_hash));
    }

    private function addFailedLogin($user_id) {
        return $this->db->query("INSERT INTO failed_logins (failed_login_user_id) VALUES (:user_id)", array("user_id" => $user_id));
    }

    private function clearFailedLogins($user_id) {
        return $this->db->query("DELETE FROM failed_logins WHERE failed_login_user_id = :user_id", array("user_id" => $user_id));
    }

    private function getFailedLoginsCount($user_id) {
        return $this->db->queryOne("SELECT COUNT(*) FROM failed_logins WHERE failed_login_user_id = :id", array("id" => $user_id));
    }

    private function getLastFailedLoginTimestamp($user_id) {
        return $this->db->queryOne("SELECT failed_login_created_at FROM failed_logins WHERE failed_login_user_id = :id ORDER BY failed_login_id DESC LIMIT 1", array("id" => $user_id));
    }

    private function getUserByEmail($user_email) {
        return $this->db->queryRow("SELECT user_id, user_is_actived, user_password_salt, user_password_hash FROM users WHERE user_email = :user_email", array("user_email" => $user_email));
    }

    private function getUserByToken($token) {
        $token_hash = $this->getLoginTokenHash($token);


    }

    // ------------------------------------------------------------------------
    // Public methods.
    // ------------------------------------------------------------------------

    /**
     * AuthModel konštruktor.
     */
    public function __construct() {
        try {
            $this->db = Database::getInstance();
        } catch (Exception $e) {
            throw new Exception("Nastal problém s databázou.");
        }
        self::$classLock = true;
    }

    /**
     * Vráti true, ak je užívateľ prihlásený, inak false.
     * @return bool
     */
    public function isLoggedIn() {
        if (!$this->hasLoginToken()) return false;

        $token      = $this->getLoginToken();
        $token_hash = $this->getLoginTokenHash($token);

        if (0 == $this->db->queryOne("SELECT COUNT(*) FROM active_logins WHERE active_login_token_hash = :token_hash", array("token_hash" => $token_hash))) {
            $this->removeLoginToken();
            return false;
        }
        return true;
    }

    /**
     * Prihlási užívateľa pomocou emailu a hesla, inak vyhodí výnimku.
     * @param string $email
     * @param string $password
     * @throws Exception
     */
    public function doLogin($email, $password) {

        if (!$this->isValidEmail($email)) {
            throw new Exception("Zadaný email nemá správny tvar.");
        }

        if (!$this->isValidPassword($password)) {
            throw new Exception("Zadané heslo nemá správny tvar.");
        }

        $user = $this->getUserByEmail($email);

        if (empty($user) || !isset($user->user_id)) {
            throw new Exception("Účet neexistuje.");
        }

        if (false == $user->user_is_actived) {
            throw new Exception("Učet nie je aktivovaný.");
        }

        $failed_logins_count = $this->getFailedLoginsCount($user->user_id);

        if (0 != $failed_logins_count) {
            $dt1 = new DateTime($this->getLastFailedLoginTimestamp($user->user_id));
            $dt2 = new DateTime("-30 seconds");

            if ($failed_logins_count > 3 && $dt1 > $dt2) {
                throw new Exception("Musíte počkať 30 sekúnd.");
            }
        }

        $password_hash = $user->user_password_hash;
        $password_salt = $user->user_password_salt;

        if ($password_hash != $this->getPasswordHash($password, $password_salt)) {
            $this->addFailedLogin($user->user_id);
            throw new Exception("Nesprávne heslo.");
        }

        $token = $this->getNewLoginToken();
        $this->setLoginToken($token);

        $token_hash = $this->getLoginTokenHash($token);
        $this->addActiveLogin($user->user_id, $token_hash);
        $this->clearFailedLogins($user->user_id);
    }

    /**
     * Odhlási užívateľa.
     */
    public function doLogout() {
        $token      = $this->getLoginToken();
        $token_hash = $this->getLoginTokenHash($token);
        $this->removeActiveLogin($token_hash);
        $this->removeLoginToken();
    }

    /**
     * Zaregistruje nového užívateľa.
     * @param string $email
     * @param string $password
     * @param string $password_again
     * @throws Exception
     */
    public function doRegistration($email, $password, $password_again) {

        if (!$this->isValidEmail($email)) {
            throw new Exception("Zadaný email nemá správny tvar.");
        }

        if (!$this->isFreeEmail($email)) {
            throw new Exception("Zadaný email už používa iný účet.");
        }

        if (!$this->isValidPassword($password)) {
            throw new Exception("Zadané heslo musí mať minimálne 6 znakov.");
        }

        if ($password != $password_again) {
            throw new Exception("Zadané heslá sa nezhodujú.");
        }

        $password_salt = $this->getPasswordSalt();
        $password_hash = $this->getPasswordHash($password, $password_salt);

        $activation_key      = $this->getActivationKey();
        $activation_key_hash = $this->getActivationKeyHash($activation_key);

        $query = "
            INSERT INTO users (
              user_email, 
              user_password_hash, 
              user_password_salt,
              user_activation_key_hash
            ) VALUES (
              :email,
              :password_hash,
              :password_salt,
              :activation_key_hash
            )
        ";

        $param = array(
            "email"               => $email,
            "password_hash"       => $password_hash,
            "password_salt"       => $password_salt,
            "activation_key_hash" => $activation_key_hash
        );

        try {
            $this->db->query($query, $param);
        } catch (Exception $e) {
            throw $e;
        }

        // todo: send activation key!!

    }

    public function useActivationKey($email, $key) {
    }

    public function sendRecoveryKey($email) {
    }

    public function useRecoveryKey($email, $key, $password, $password) {
    }

    /* public function updateEmail($email) {

        if (!$this->isValidEmail($email)) {
            throw new Exception("Zadaný email nemá správny tvar.");
        }

        if (!$this->isFreeEmail($email)) {
            throw new Exception("Zadaný email už používa iný účet.");
        }

        $query = "UPDATE users SET user_email = :email WHERE user_id = :id";
        $param = array(
            "email" => $email,
            "id"    => $this->getUserIdByToken()
        );

        try {
            $this->db->query($query, $param);
        } catch (Exception $e) {
            throw new Exception("Zmena emailu zlyhala.");
        }
    } */

    public function updatePassword($password_old, $password_new, $password_new_again) {
    }

}
