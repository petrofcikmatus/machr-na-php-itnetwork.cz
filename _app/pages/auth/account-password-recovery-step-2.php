<?php

$am = new AuthModel();

//if ($am->isLoggedIn()) redirect("moj-profil");

$email         = isset($_GET["email"]) ? $_GET["email"] : (isset($_POST["email"]) ? $_POST["email"] : "");
$key           = isset($_GET["key"]) ? $_GET["key"] : (isset($_POST["key"]) ? $_POST["key"] : "");
$password      = isset($_POST["password"]) ? $_POST["password"] : "";
$passwordAgain = isset($_POST["passwordAgain"]) ? $_POST["passwordAgain"] : "";

if (is_post_request()) {
    try {
        $am->useRecoveryKey($email, $key, $password, $passwordAgain);
        add_message("Obnova hesla prebehla úspešne.");
        redirect("prihlasenie?email=" . $email);
    } catch (Exception $e) {
        add_message($e->getMessage());
    }
}

add_layout("header", array("title" => "Obnova hesla - použitie obnovovacieho kľúča"));

?>

    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="text-center">Použitie obnovovacieho kľúča</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <form class="form-horizontal">
                    <div class="form-group">
                        <label for="inputEmail" class="col-lg-2 control-label">Email</label>
                        <div class="col-lg-10">
                            <input class="form-control" id="inputEmail" placeholder="Email" type="text" value="<?= plain($email) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputKey" class="col-lg-2 control-label">Obnovovací kľúč</label>
                        <div class="col-lg-10">
                            <input class="form-control" id="inputKey" placeholder="Obnovovací kľúč" type="text" value="<?= plain($key) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputPassword" class="col-lg-2 control-label">Heslo</label>
                        <div class="col-lg-10">
                            <input class="form-control" id="inputPassword" placeholder="Heslo" type="password" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputPasswordAgain" class="col-lg-2 control-label">Heslo znovu</label>
                        <div class="col-lg-10">
                            <input class="form-control" id="inputPasswordAgain" placeholder="Heslo znovu" type="password" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-lg-10 col-lg-offset-2">
                            <button type="submit" class="btn btn-primary">Obnoviť heslo</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php add_layout("footer") ?>