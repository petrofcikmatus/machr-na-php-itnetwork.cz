<?php

/**
 * Class SuperMail
 * A už sa mi fakt nechce mailovať takto. Škoda že nemôžeme použiť mailovú knižnicu cez composer abo čo :/
 * Apoň si vyskúšam previazanie metód :)
 */
class SuperMail {

    private $from     = "matus@petrofcik.eu";
    private $fromName = "Matúš Petrofčík";
    private $to       = "";
    private $subject  = "Without subject?";
    private $content  = "Nothing! Ooops :)";

    /**
     * @param $email
     * @return $this
     */
    public function setFrom($email) {
        $this->from = $email;
        return $this;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setFromName($name) {
        $this->fromName = $name;
        return $this;
    }

    /**
     * @param $email
     * @return $this
     */
    public function setTo($email) {
        $this->to = $email;
        return $this;
    }

    /**
     * @param $content
     * @return $this
     */
    public function setContent($content) {
        $this->content = $content;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function send() {
        $header = "From: <{$this->fromName}>{$this->from}\r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-Type: text/html; charset=\"utf-8\"\r\n";

        if (!mb_send_mail($this->to, $this->subject, $this->content, $header)) {
            throw new Exception("Nedá sa mi vyslať mail... :'(");
        }
    }

    /**
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject) {
        $this->subject = $subject;
        return $this;
    }
}
