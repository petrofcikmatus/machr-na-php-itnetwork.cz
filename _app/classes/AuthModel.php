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

    /** @var Database */
    private $db;

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
     * Vráti true, ak je email voľný, inak false.
     * @param string $email
     * @return bool
     */
    private function isFreeEmail($email) {
        return 0 == $this->db->queryOne("SELECT COUNT(*) FROM users WHERE email = :email", array("email" => $email));
    }

    /**
     * Vyhodí výnimku ak nastane problém.
     * @param string $email
     * @param bool $check_is_free
     * @throws Exception
     */
    private function validateEmail($email, $check_is_free = false) {

        // ak email nie je zadaný
        if ("" == $email) {
            throw new Exception("Nezadali ste email.");
        }

        // ak to nie je email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Zadaný email nemá správny tvar.");
        }

        // ak chcem overiť dostupnosť emailu a nie je voľný
        if ($check_is_free && !$this->isFreeEmail($email)) {
            throw new Exception("Zadaný email už používa iný účet.");
        }
    }

    /**
     * Vyhodí výnimku ak nastane problém.
     * @param string $password
     * @param null $password_again
     * @throws Exception
     */
    private function validatePassword($password, $password_again = null) {

        // ak nie je heslo zadané
        if ("" == $password) {
            throw new Exception("Nezadali ste heslo.");
        }

        // ak je heslo príliš krátke
        if (mb_strlen($password, "utf-8") < 6) {
            throw new Exception("Zadané heslo nemá minimálne 6 znakov.");
        }

        // ak máme aj kontrolné heslo
        if (null != $password_again) {

            // ak je kontrolné heslo prázdne
            if ("" == $password_again) {
                throw new Exception("Nezadali ste kontrolné heslo.");
            }

            // ak sa heslá nezhodujú
            if ($password != $password_again) {
                throw new Exception("Zadané heslá sa nezhodujú");
            }
        }
    }

    /**
     * Vyhodí výnimku ak nastane problém.
     * @param string $key
     * @throws Exception
     */
    private function validateKey($key) {
        // ak kľúč nie je zadaný
        if ("" == $key) {
            throw new Exception("Nezadali ste kľúč.");
        }
    }

    /**
     * Vyhodí výnimku ak nastane problém.
     * Todo: ja som túto funkcionalitu schválne nepoužil.
     * @param string $name
     * @throws Exception
     */
    private function validateName($name) {

        // ak meno nie je zadané
        if ("" == $name) {
            throw new Exception("Nezadali ste heslo.");
        }

        // ak je meno príliš krátne
        if (mb_strlen($name, "utf-8") < 3) {
            throw new Exception("Zadané meno nemá minimálne 3 znaky.");
        }

        // ak je meno príliš dlhé
        if (mb_strlen($name, "utf-8") > 64) {
            throw new Exception("Zadané heslo nemá maximálne 64 znakov.");
        }
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
     * @return null
     */
    private function getLoginToken() {
        return $this->hasLoginToken() ? $_COOKIE[self::$tokenCookieName] : null;
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

    /**
     * Pridá nové aktívne prihlásenie.
     * @param int $uid
     * @param string $token
     * @return int
     */
    private function addActiveLogin($uid, $token) {
        return $this->db->query("INSERT INTO active_logins (uid, token_hash) VALUES (:uid, :token_hash)", array("uid" => $uid, "token_hash" => $this->getTokenHash($token)));
    }

    /**
     * Vráti true ak pre daný token existuje aktívne prihlásenie.
     * @param string $token
     * @return bool
     */
    private function hasActiveLogin($token) {
        return 1 == $this->db->queryOne("SELECT COUNT(*) FROM active_logins WHERE token_hash = :token_hash", array("token_hash" => $this->getTokenHash($token)));
    }

    /**
     * Vymaže dané aktívne prihlásenie.
     * @param string $token
     * @return int
     */
    private function removeActiveLogin($token) {
        return $this->db->query("DELETE FROM active_logins WHERE token_hash = :token_hash", array("token_hash" => $this->getTokenHash($token)));
    }

    // Failed logins.

    /**
     * Pridá užívateľovi chybné prihlásenie.
     * @param int $uid
     * @return int
     */
    private function addFailedLogin($uid) {
        return $this->db->query("INSERT INTO failed_logins (uid) VALUES (:uid)", array("uid" => $uid));
    }

    /**
     * Vymaže užívateľovi chybné prihlásenia.
     * @param int $uid
     * @return int
     */
    private function removeFailedLogins($uid) {
        return $this->db->query("DELETE FROM failed_logins WHERE uid = :uid", array("uid" => $uid));
    }

    /**
     * Vráti počet chybných prihlásení daného užívateľa.
     * @param int $uid
     * @return mixed
     */
    private function getFailedLoginsCount($uid) {
        return $this->db->queryOne("SELECT COUNT(*) FROM failed_logins WHERE uid = :uid", array("uid" => $uid));
    }

    /**
     * Vráti dáta o poslednom chybnom prihlásení.
     * @param int $uid
     * @return mixed
     */
    private function getLastFailedLogin($uid) {
        return $this->db->queryRow("SELECT * FROM failed_logins WHERE uid = :uid ORDER BY id DESC LIMIT 1", array("uid" => $uid));
    }

    // User ID.

    /**
     * Vráti ID užívateľa podľa emailu.
     * @param string $email
     * @return mixed
     */
    private function getUserIdByEmail($email) {
        return $this->db->queryOne("SELECT id FROM users WHERE email = :email", array("email" => $email));
    }

    /**
     * Vráti ID užívateľa podľa tokenu.
     * @param string $token
     * @return mixed
     */
    private function getUserIdByToken($token) {
        $token_hash = $this->getTokenHash($token);
        return $this->db->queryOne("SELECT uid FROM active_logins WHERE token_hash = :token_hash", array("token_hash" => $token_hash));
    }

    // User data.

    /**
     * Vráti užívateľské dáta podľa ID.
     * @param int $uid
     * @return mixed
     */
    private function getUserDataById($uid) {
        return $this->db->queryRow("SELECT * FROM users WHERE id = :uid", array("uid" => $uid));
    }

    /**
     * Vráti užívateľské dáta podľa emailu.
     * @param string $email
     * @return mixed
     */
    private function getUserDataByEmail($email) {
        return $this->db->queryRow("SELECT * FROM users WHERE email = :email", array("email" => $email));
    }

    /**
     * Vráti užívateľské dáta podľa tokenu.
     * @param string $token
     * @return mixed
     */
    private function getUserDataByToken($token) {
        return $this->getUserDataById($this->getUserIdByToken($token));
    }

    // Recovery keys.

    /**
     * Pridá nový obnovovací kľúč.
     * @param int $uid
     * @param int $key
     * @return int
     */
    private function addRecoveryKey($uid, $key) {
        return $this->db->query("INSERT INTO recovery_keys (uid, key_hash) VALUES (:uid, :key_hash)", array("uid" => $uid, "key_hash" => $this->getKeyHash($key)));
    }

    /**
     * Vráti počet dostupných obnovovacích kľúčov daného užívateľa.
     * @param int $uid
     * @return mixed
     */
    private function getRecoveryKeysCount($uid) {
        return $this->db->queryOne("SELECT COUNT(*) FROM recovery_keys WHERE uid = :uid", array("uid" => $uid));
    }

    /**
     * Vráti posledný záznam o obnovovacom kľúči daného užívateľa.
     * @param int $uid
     * @return mixed
     */
    private function getLastRecoveryKey($uid) {
        return $this->db->queryRow("SELECT * FROM recovery_keys WHERE uid = :uid ORDER BY id DESC LIMIT 1", array("uid" => $uid));
    }

    /**
     * Vymaže obnovovacie kľúče daného užívateľa.
     * @param int $uid
     * @return int
     */
    private function removeRecoveryKeys($uid) {
        return $this->db->query("DELETE FROM recovery_keys WHERE uid = :uid", array("uid" => $uid));
    }

    // Others.

    /**
     * Aktivuje danému užívateľovi účet a vymaže hash aktivačného kľúča (už ho nebude potrebovať).
     * @param int $uid
     * @return int
     */
    private function setUserActived($uid) {
        return $this->db->query("UPDATE users SET is_actived = TRUE, activation_key_hash = '' WHERE id = :uid", array("uid" => $uid));
    }

    // ------------------------------------------------------------------------
    // Public methods.
    // ------------------------------------------------------------------------

    /**
     * AuthModel konštruktor.
     * Môj databázový wrapper môžeme pridať pomocou DI alebo sa pridá staticky sám.
     * @param Database $database
     * @throws Exception
     */
    public function __construct(Database $database = null) {
        if (null == $database) {
            try {
                $this->db = Database::getInstance();
            } catch (Exception $e) {
                throw new Exception("Nastal problém s databázou.", 0, $e);
            }
        } else {
            $this->db = $database;
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
     * Prihlásenie užívateľa pomocou emailu a hesla, inak vyhodí výnimku.
     * @param string $email
     * @param string $password
     * @throws Exception
     */
    public function doLogin($email, $password) {

        $this->validateEmail($email);
        $this->validatePassword($password);

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
                $seconds = $dt1->format("s") - $dt2->format("s");
                throw new Exception("Musíte počkať ${seconds} sekúnd.");
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
     * Odhlásenie užívateľa.
     */
    public function doLogout() {
        $token = $this->getLoginToken();
        $this->removeActiveLogin($token);
        $this->removeLoginToken();
    }

    /**
     * Zaregistrovanie nového užívateľa.
     * @param string $email
     * @param string $password
     * @param string $password_again
     * @throws Exception
     */
    public function doRegistration($email, $password, $password_again) {

        $this->validateEmail($email, true);

        $this->validatePassword($password, $password_again);

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

        try {
            $message = "<html><body>";
            $message .= "<br />Váš aktivačný kľúč je: <b>" . $activation_key . "</b>";
            $message .= "</html></body>";

            $mailer = new SuperMail();
            $mailer->setFrom("email@localhost")->setFromName("Localhost")->setTo($email)->setSubject("Aktivačný kľúč")->setContent($message)->send();
        } catch (Exception $e) {
            // todo: zakomentované schválne, na localhoste mi nejde posielať mail!
            // throw new Exception("Počas posielania emailu s aktivačným kľúčom nastala neočakávaná chyba.", 0, $e);
        }

        // todo: toto by tu samozrejme nemalo byť, ale nechávam z dôvodu skúšania apky na localhoste ;)
        add_message("Aktivačný kľúč: " . $activation_key);
    }

    /**
     * Použitie aktivačného kľúča.
     * @param string $email
     * @param string $key
     * @throws Exception
     */
    public function useActivationKey($email, $key) {

        $this->validateEmail($email);

        $this->validateKey($key);

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
            $this->setUserActived($user->id);
        } catch (Exception $e) {
            throw new Exception("Počas aktivácie účtu nastala neočakávaná chyba.", 0, $e);
        }
    }

    /**
     * Zaslanie obnovovacieho kľúča.
     * @param string $email
     * @throws Exception
     */
    public function sendRecoveryKey($email) {

        $this->validateEmail($email);

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

        try {
            $message = "<html><body>";
            $message .= "<br />Váš obnovovací kľúč je: <b>" . $recovery_key . "</b>";
            $message .= "</html></body>";

            $mailer = new SuperMail();
            $mailer->setFrom("email@localhost")->setFromName("Localhost")->setTo($email)->setSubject("Obnovovací kľúč")->setContent($message)->send();
        } catch (Exception $e) {
            // todo: zakomentované schválne, na localhoste mi nejde posielať mail!
            // throw new Exception("Počas posielania emailu s obnovovacím kľúčom nastala neočakávaná chyba.", 0, $e);
        }

        // todo: toto by tu samozrejme nemalo byť, ale nechávam z dôvodu skúšania apky na localhoste ;)
        add_message("Obnovovací kľúč: " . $recovery_key);
    }

    /**
     * Použitie obnovovacieho kľúča.
     * @param string $email
     * @param string $key
     * @param string $password
     * @param string $password_again
     * @throws Exception
     */
    public function useRecoveryKey($email, $key, $password, $password_again) {

        $this->validateEmail($email);

        $this->validateKey($key);

        $this->validatePassword($password, $password_again);

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

    /**
     * Zmena emailu prihlásenému užívateľovi.
     * @param $email
     * @throws Exception
     */
    public function updateEmail($email) {
        $this->validateEmail($email, true);
        $id = $this->getUserIdByToken($this->getLoginToken());
        try {
            $this->db->query("UPDATE users SET email = :email WHERE id = :id", array("email" => $email, "id" => $id));
        } catch (Exception $e) {
            throw new Exception("Počas zmeny emailu nastala neočakávaná chyba.", 0, $e);
        }
    }

    /**
     * Zmena hesla prihlásenému užívateľovi.
     * @param $password_old
     * @param $password_new
     * @param $password_new_again
     * @throws Exception
     */
    public function updatePassword($password_old, $password_new, $password_new_again) {
        $this->validatePassword($password_old);
        $this->validatePassword($password_new, $password_new_again);

        $user = $this->getUserDataByToken($this->getLoginToken());

        if (empty($user) || !isset($user->id)) {
            throw new Exception("Chyťte ho! Niktoša jedneho!");
        }

        $password_old_hash = $this->getPasswordHash($password_old, $user->password_salt);

        if ($password_old_hash != $user->password_hash) {
            throw new Exception("Zadané staré heslo nie je správne.");
        }

        $password_new_salt = $this->generateSalt();
        $password_new_hash = $this->getPasswordHash($password_new, $password_new_salt);

        $param = array(
            "id"            => $user->id,
            "password_salt" => $password_new_salt,
            "password_hash" => $password_new_hash
        );

        try {
            $this->db->query("UPDATE users SET password_salt = :password_salt, password_hash = :password_hash WHERE id = :id", $param);
        } catch (Exception $e) {
            throw new Exception("Počas zmeny hesla nastala neočakávaná chyba.", 0, $e);
        }
    }

    /**
     * Vráti dáta o užívateľovi, buď o prihlásenom alebo podľa ID.
     * Toto by som dal niekam inam, do triedy ktorá sa stará o detaily užívateľov a tak.
     * Podľa mňa to sem nepatrí pretože táto trieda by sa mala starať výhradne o autentifikáciu užívateľa :)
     * @param null|int $uid
     * @return mixed
     */
    public function getUserData($uid = null) {
        if (null == $uid) {
            $user = $this->getUserDataByToken($this->getLoginToken());
        } else {
            $user = $this->getUserDataById($uid);
        }

        $dt = new DateTime($user->created_at);

        $user->created_at = $dt->format("d.m.Y H:i:s");

        return $user;
    }
}
