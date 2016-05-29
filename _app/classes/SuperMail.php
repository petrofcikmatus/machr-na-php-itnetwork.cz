<?php

/**
 * Class SuperMail
 * A už sa mi fakt nechce mailovať takto. Škoda že nemôžeme použiť mailovú knižnicu cez composer abo čo :/
 * Apoň si vyskúšam previazanie metód :)
 */
class SuperMail {

    private $from     = "no-reply@nazovdomeny.eu"; // u websupportu sa dá posielať len z toho mailu, ktorý máme vytvorený
    private $to       = "";
    private $subject  = "Without subject?";
    private $content  = "Nothing! Ooops :)";

    /**
     * @param $email
     * @return $this
     */
    public function setTo($email) {
        $this->to = $email;
        return $this;
    }

    /**
     * @param string $subject
     * @return $this
     */
    public function setSubject($subject) {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @param $content
     * @return $this
     */
    public function setContent($content) {
        $this->content = $message = wordwrap($content, 70, "\r\n");
        return $this;
    }

    /**
     * @throws Exception
     */
    public function send() {

        $headers  = "From: Machr na PHP <" . $this->from . ">\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=\"utf-8\"\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";

        if (!mail($this->to, $this->subject, $this->content, $headers)) {
            throw new Exception("Nedá sa mi vyslať mail... :'(");
        }
    }
}
