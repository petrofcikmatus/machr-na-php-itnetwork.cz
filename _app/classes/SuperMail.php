<?php

/**
 * Class SuperMail
 * A už sa mi fakt nechce mailovať takto. Škoda že nemôžeme použiť mailovú knižnicu cez composer abo čo :/
 * Apoň si vyskúšam previazanie metód :)
 */
class SuperMail {

    private $from     = "uid141564@web.websupport.sk"; // u websupportu sa dá posielať len z toho mailu, ktorý máme vytvorený
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
        $this->content = $message = wordwrap($content, 70, "\r\n");;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function send() {

        $header = "From: Machr na PHP <" . $this->from . ">\r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-Type: text/html; charset=\"utf-8\"\r\n";
        
        if (!mail($this->to, $this->subject, $this->content, $header)) {
            throw new Exception("Nedá sa mi vyslať mail... :'(");
        }
    }
}
