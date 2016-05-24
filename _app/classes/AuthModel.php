<?php

/**
 * Class AuthModel
 */
class AuthModel {

    // ------------------------------------------------------------------------
    // Properties.
    // ------------------------------------------------------------------------

    private static $privateKey = "";
    private static $tokenName = "auth_token";
    private static $classLock = false;

    private $db = null;

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

    public function doLogin($email, $password) {

        if (!$this->isValidEmail($email)) {
            throw new Exception("Prihlasovací email má nesprávny tvar.");
        }

        if (!$this->isValidPassword($password)) {
            throw new Exception("Prihlasovacie heslo má nesprávny tvar.");
        }

        $user = $this->db->queryRow("SELECT user_id, user_is_actived, user_password_salt, user_password_hash FROM users WHERE user_email = :email", array("email" => $email));

        if (empty($user) || !isset($user->user_id)) {
            throw new Exception("Účet neexistuje.");
        }

        if (false == $user->user_is_actived) {
            throw new Exception("Učet nebol aktivovaný.");
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

        $this->clearFailedLogins($user->user_id);
        $token = $this->generateLoginToken();
        $this->addActiveLogin($user->user_id, $token);
        $this->setLoginToken($token);
    }

    private function addFailedLogin($user_id) {
        return $this->db->query("INSERT INTO failed_logins (failed_login_user_id) VALUES (:user_id)", array("user_id" => $user_id));
    }

    private function getFailedLoginsCount($user_id) {
        return $this->db->queryOne("SELECT COUNT(*) FROM failed_logins WHERE failed_login_user_id = :id", array("id" => $user_id));
    }

    private function getLastFailedLoginTimestamp($user_id) {
        return $this->db->queryOne("SELECT failed_login_created_at FROM failed_logins WHERE failed_login_user_id = :id ORDER BY failed_login_id DESC LIMIT 1", array("id" => $user_id));
    }

    private function clearFailedLogins($user_id) {
        return $this->db->query("DELETE FROM failed_logins WHERE failed_login_user_id = :user_id", array("user_id" => $user_id));
    }

    private function addActiveLogin($user_id, $token) {
        return $this->db->query("INSERT INTO active_logins (active_login_user_id, active_login_token) VALUES (:user_id, :token)", array("user_id" => $user_id, "token" => $token));
    }

    public function doLogout() {
        $this->db->query("DELETE FROM active_logins WHERE active_login_token = :token LIMIT 1", array("token" => $this->getLoginToken()));
        $this->removeLoginToken();
    }

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
              user_activation_hash
            ) VALUES (
              :email,
              :password_hash,
              :password_salt,
              :activation_hash
            )
        ";

        $param = array(
            "email"           => $email,
            "password_hash"   => $password_hash,
            "password_salt"   => $password_salt,
            "activation_hash" => $activation_key_hash
        );

        try {
            $this->db->query($query, $param);
        } catch (Exception $e) {
            throw $e;
        }

    }

    public function useActivationKey($email, $key) {
    }

    public function sendRecoveryKey($email) {
    }

    public function useRecoveryKey($email, $key, $password, $password) {
    }

    public function updateEmail($email) {

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
    }

    public function updatePassword($password_old, $password_new, $password_new_again) {
    }

    /**
     * @return bool
     */
    public function isLoggedIn() {
        if (!$this->hasLoginToken()) return false;

        $query      = "SELECT COUNT(*) FROM active_logins WHERE active_login_token = :token";
        $parameters = array("token" => $this->getLoginToken());

        if (0 == $this->db->queryOne($query, $parameters)) {
            $this->removeLoginToken();
            return false;
        }

        return true;
    }

    // ------------------------------------------------------------------------
    // Private methods.
    // ------------------------------------------------------------------------

    /**
     * Vráti true, ak je email valídny, inak false.
     * @param $email
     * @return mixed
     */
    private function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Vráti true, ak je heslo dostačujúce, inak false.
     * @param $password
     * @return bool
     */
    private function isValidPassword($password) {
        return (mb_strlen($password, "utf-8") > 5);
    }

    /**
     * Vráti true, ak je email voľný, inak false.
     * @param $email
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
     * @return string
     */
    private function getPasswordSalt() {
        return hash("sha512", mt_rand() . time() . uniqid());
    }

    /**
     * Vráti náhodne vygenerovaný 6-znakový aktivačný kľúč.
     * Metóda sa môže meniť aj po nasadení do produkcie.
     * @return string
     */
    private function getActivationKey() {
        return substr(strtoupper(hash("sha1", mt_rand() . time() . uniqid())), 0, 6);
    }

    /**
     * Vráti náhodne vygenerovaný 128-znakový prihlasovací token.
     * Metóda sa môže meniť aj po nasadení do produkcie.
     * @return string
     */
    private function generateLoginToken() {
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
     * Vráti true, ak existuje prihlasovací token, inak false.
     * @return bool
     */
    private function hasLoginToken() {
        return isset($_COOKIE[self::$tokenName]);
    }

    /**
     * Vráti prihlasovací token.
     * @param null $default
     * @return null
     */
    private function getLoginToken($default = null) {
        return $this->hasLoginToken() ? $_COOKIE[self::$tokenName] : $default;
    }

    /**
     * Pridá cookie s prihlasovacím tokenom.
     * @param string $token
     * @return bool
     */
    private function setLoginToken($token) {
        return setcookie(self::$tokenName, $token, strtotime("+30 days"));
    }

    /**
     * Odstráni cookie s prihlasovacím tokenom.
     * @return bool
     */
    private function removeLoginToken() {
        return setcookie(self::$tokenName, null, 1);
    }

    private function getUserIdByToken() {
        if (!$this->isLoggedIn()) return null;

        $query = "SELECT active_login_user_id FROM active_logins WHERE active_login_token = :token";
        $param = array(
            "token" => $this->getLoginToken()
        );

        return $this->db->queryOne($query, $param);
    }

    private function getDataById($user_id) {
        $query = "
            SELECT u.user_id, 
                   u.user_email, 
                   u.user_password_salt, 
                   u.user_password_hash , 
                   u.user_is_actived, 
                   u.user_created_at
            FROM users u 
            LEFT JOIN active_logins al ON (u.user_id = al.active_login_user_id)
            LEFT JOIN failed_logins fl ON (u.user_id = fl.failed_login_user_id)
            WHERE u.user_id = :user_id
        ";
        $param = array("user_id" => $user_id);

        return $this->db->queryRow($query, $param);
    }

    private function getDataByToken($active_login_token) {
        $query = "";
        $param = array("active_login_token" => $active_login_token);

        return $this->db->queryRow($query, $param);
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
            throw new Exception("Zmena privátneho kľúča už nie je možná.");
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
            throw new Exception("Zmena názvu tokenu už nie je možná.");
        }
        self::$tokenName = $tokenName;
    }
}
