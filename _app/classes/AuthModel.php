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

    // Validators.

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
        return $this->isValidEmail($email) && 0 == $this->db->queryOne("SELECT COUNT(*) FROM users WHERE email = :email", array("email" => $email));
    }

    // Generators.

    /**
     * Vráti náhodne vygenerovanú 128-znakovú soľ.
     * Aj keby mali užívatelia rovnaké heslá, tak sa to nezistí :)
     * Metóda sa môže meniť aj po nasadení do produkcie.
     * todo: je niečo efektívnejšie pre generovanie takého stringu?
     * @return string
     */
    private function generateSalt() {
        return hash("sha512", mt_rand() . time() . uniqid());
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
     * Vráti náhodne vygenerovaný 6-znakový kľúč.
     * Metóda sa môže meniť aj po nasadení do produkcie.
     * todo: je niečo efektívnejšie pre generovanie takého stringu?
     * @return string
     */
    private function generateKey() {
        return substr(strtoupper(hash("sha1", mt_rand() . time() . uniqid())), 0, 6);
    }

    /**
     * Vráti 128-znakový hash aktivačného kľúča.
     * Metóda sa po nasadení do produkcie nemôže meniť.
     * todo: je niečo efektívnejšie pre generovanie takého stringu?
     * @param string $key
     * @return string
     */
    private function getKeyHash($key) {
        return hash("sha512", $key);
    }

    /**
     * Vráti náhodne vygenerovaný 128-znakový prihlasovací token.
     * Metóda sa môže meniť aj po nasadení do produkcie.
     * todo: je niečo efektívnejšie pre generovanie takého stringu?
     * @return string
     */
    private function generateToken() {
        return hash("sha512", mt_rand() . time() . uniqid());
    }

    /**
     * Vráti hash prihlasovacieho tokenu.
     * Metóda sa po nasadení do produkcie nemôže meniť.
     * @param string $token
     * @return string
     */
    private function getTokenHash($token) {
        return hash("sha512", $token . self::$secretTokenSalt);
    }

    // Login token.

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

    // Active logins.

    private function addActiveLogin($uid, $token) {
        return $this->db->query("INSERT INTO active_logins (uid, token_hash) VALUES (:uid, :token_hash)", array("uid" => $uid, "token_hash" => $this->getTokenHash($token)));
    }

    private function hasActiveLogin($token) {
        return 1 == $this->db->queryOne("SELECT COUNT(*) FROM active_logins WHERE token_hash = :token_hash", array("token_hash" => $this->getTokenHash($token)));
    }

    private function removeActiveLogin($token) {
        return $this->db->query("DELETE FROM active_logins WHERE token_hash = :token_hash", array("token_hash" => $this->getTokenHash($token)));
    }

    // Failed logins.

    private function addFailedLogin($uid) {
        return $this->db->query("INSERT INTO failed_logins (uid) VALUES (:uid)", array("uid" => $uid));
    }

    private function removeFailedLogins($uid) {
        return $this->db->query("DELETE FROM failed_logins WHERE uid = :uid", array("uid" => $uid));
    }

    private function getFailedLoginsCount($uid) {
        return $this->db->queryOne("SELECT COUNT(*) FROM failed_logins WHERE uid = :uid", array("uid" => $uid));
    }

    private function getLastFailedLogin($uid) {
        return $this->db->queryRow("SELECT * FROM failed_logins WHERE uid = :uid ORDER BY id DESC LIMIT 1", array("uid" => $uid));
    }

    // User ID.

    private function getUserIdByEmail($email) {
        return $this->db->queryOne("SELECT id FROM users WHERE email = :email", array("email" => $email));
    }

    private function getUserIdByToken($token) {
        $token_hash = $this->getTokenHash($token);
        return $this->db->queryOne("SELECT uid FROM active_logins WHERE token_hash = :token_hash", array("token_hash" => $token_hash));
    }

    // User data.

    private function getUserDataById($id) {
        return $this->db->queryRow("SELECT * FROM users WHERE id = :id", array("id" => $id));
    }

    private function getUserDataByEmail($email) {
        return $this->db->queryRow("SELECT * FROM users WHERE email = :email", array("email" => $email));
    }

    private function getUserDataByToken($token) {
        return $this->getUserDataById($this->getUserIdByToken($token));
    }

    // Recovery keys.

    private function addRecoveryKey($uid, $key) {
        return $this->db->query("INSERT INTO recovery_keys (uid, key_hash) VALUES (:uid, :key_hash)", array("uid" => $uid, "key_hash" => $this->getKeyHash($key)));
    }

    private function getRecoveryKeysCount($uid) {
        return $this->db->queryOne("SELECT COUNT(*) FROM recovery_keys WHERE uid = :uid", array("uid" => $uid));
    }

    private function getLastRecoveryKey($uid) {
        return $this->db->queryRow("SELECT * FROM recovery_keys WHERE uid = :uid ORDER BY id DESC LIMIT 1", array("uid" => $uid));
    }

    private function removeRecoveryKeys($uid) {
        return $this->db->query("DELETE FROM recovery_keys WHERE uid = :uid", array("uid" => $uid));
    }

    // Others.

    private function setUserActive($id) {
        return $this->db->query("UPDATE users SET is_actived = TRUE, activation_key_hash = '' WHERE id = :id", array("id" => $id));
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

        $token = $this->getLoginToken();

        if (!$this->hasActiveLogin($token)) {
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

        $user = $this->getUserDataByEmail($email);

        if (empty($user) || !isset($user->id)) {
            throw new Exception("Účet neexistuje.");
        }

        if (false == $user->is_actived) {
            throw new Exception("Učet nie je aktivovaný.");
        }

        $failed_logins_count = $this->getFailedLoginsCount($user->id);

        if (0 != $failed_logins_count) {
            $last_failed_login = $this->getLastFailedLogin($user->id);

            $dt1 = new DateTime($last_failed_login->created_at);
            $dt2 = new DateTime("-30 seconds");

            if ($failed_logins_count > 3 && $dt1 > $dt2) {
                throw new Exception("Musíte počkať 30 sekúnd.");
            }
        }

        $password_hash = $user->password_hash;
        $password_salt = $user->password_salt;

        if ($password_hash != $this->getPasswordHash($password, $password_salt)) {
            $this->addFailedLogin($user->id);
            throw new Exception("Nesprávne heslo.");
        }

        $this->removeFailedLogins($user->id);

        $token = $this->generateToken();
        $this->addActiveLogin($user->id, $token);
        $this->setLoginToken($token);
    }

    /**
     * Odhlási užívateľa.
     */
    public function doLogout() {
        $token = $this->getLoginToken();
        $this->removeActiveLogin($token);
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

        $password_salt = $this->generateSalt();
        $password_hash = $this->getPasswordHash($password, $password_salt);

        $activation_key      = $this->generateKey();
        $activation_key_hash = $this->getKeyHash($activation_key);

        $query = "
            INSERT INTO users (
              email, 
              password_hash, 
              password_salt,
              activation_key_hash
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
            throw new Exception("Počas registrácie nastala neočakávaná chyba.", 0, $e);
        }

        // todo: send activation key!!
        add_message("Aktivačný kľúč: " . $activation_key);

    }

    public function useActivationKey($email, $key) {

        if (!$this->isValidEmail($email)) {
            throw new Exception("Zadaný email nemá správny tvar.");
        }

        $user = $this->getUserDataByEmail($email);

        if (empty($user) || !isset($user->id)) {
            throw new Exception("Účet neexistuje.");
        }

        if ($user->is_actived) {
            throw new Exception("Účet je už aktivovaný.");
        }

        $activation_key_hash = $this->getKeyHash($key);

        if ($user->activation_key_hash != $activation_key_hash) {
            throw new Exception("Aktivačný kľúč nie je platný.");
        }

        try {
            $this->setUserActive($user->id);
        } catch (Exception $e) {
            throw new Exception("Počas aktivácie účtu nastala neočakávaná chyba.", 0, $e);
        }
    }

    public function sendRecoveryKey($email) {

        if (!$this->isValidEmail($email)) {
            throw new Exception("Zadaný email nemá správny tvar.");
        }

        $user = $this->getUserDataByEmail($email);

        if (empty($user) || !isset($user->id)) {
            throw new Exception("Účet neexistuje.");
        }

        if (!$user->is_actived) {
            throw new Exception("Účet ešte nie je aktivovaný, nemôžete mu meniť heslo.");
        }

        $recovery_key = $this->generateKey();

        try {
            $this->addRecoveryKey($user->id, $recovery_key);
        } catch (Exception $e) {
            throw new Exception("Počas vytvárania obnovovacieho kľúča nastala neočakávaná chyba.", 0, $e);
        }

        // todo: send recovery key!!
        add_message("Obnovovací kľúč: " . $recovery_key);
    }

    public function useRecoveryKey($email, $key, $password, $password_again) {

        if (!$this->isValidEmail($email)) {
            throw new Exception("Zadaný email nemá správny tvar.");
        }

        $user = $this->getUserDataByEmail($email);

        if (empty($user) || !isset($user->id)) {
            throw new Exception("Účet neexistuje.");
        }

        if (!$user->is_actived) {
            throw new Exception("Účet ešte nie je aktivovaný, nemôžete mu meniť heslo.");
        }

        $recovery_keys_count = $this->getRecoveryKeysCount($user->id);

        if (0 == $recovery_keys_count) {
            throw new Exception("Účet nemá žiaden obnovovací kľúč.");
        }

        $last_recovery_key = $this->getLastRecoveryKey($user->id);

        $dt1 = new DateTime($last_recovery_key->created_at);
        $dt2 = new DateTime("-5 minutes");

        if ($last_recovery_key->key_hash != $this->getKeyHash($key) || $dt1 < $dt2) {
            throw new Exception("Zadaný obnovovací kľúč je neplatný.");
        }

        if (!$this->isValidPassword($password)) {
            throw new Exception("Zadané heslo musí mať minimálne 6 znakov.");
        }

        if ($password != $password_again) {
            throw new Exception("Zadané heslá sa nezhodujú.");
        }

        $password_salt = $this->generateSalt();
        $password_hash = $this->getPasswordHash($password, $password_salt);

        $query = "UPDATE users SET password_salt = :password_salt, password_hash = :password_hash WHERE id = :id";
        $param = array(
            "password_hash" => $password_hash,
            "password_salt" => $password_salt,
            "id"            => $user->id
        );

        try {
            $this->db->query($query, $param);
        } catch (Exception $e) {
            throw new Exception("Počas obnovovania hesla nastala neočakávaná chyba.", 0, $e);
        }

        $this->removeRecoveryKeys($user->id);
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
