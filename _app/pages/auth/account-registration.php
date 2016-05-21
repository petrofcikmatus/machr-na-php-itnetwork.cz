<?php

$am = new AuthModel();

//if ($am->isLoggedIn()) redirect("moj-profil");

$email         = isset($_POST["email"]) ? $_POST["email"] : "";
$password      = isset($_POST["password"]) ? $_POST["password"] : "";
$passwordAgain = isset($_POST["passwordAgain"]) ? $_POST["passwordAgain"] : "";

if (is_post_request()) {
    try {
        $am->doRegistration($email, $password, $passwordAgain);
        add_message("Registrácia prebehla úspešne.");
        redirect("prihlasenie?email=" . $email);
    } catch (Exception $e) {
        add_message($e->getMessage());
    }
}

add_layout("header", array("title" => "Registrácia"));

?>

    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="text-center">Registrácia</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <form class="form-horizontal" method="post">
                    <div class="form-group">
                        <label for="inputEmail" class="col-lg-2 control-label">Email</label>
                        <div class="col-lg-10">
                            <input class="form-control" id="inputEmail" placeholder="Email" type="text" value="<?= plain($email) ?>">
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
                            <button type="submit" class="btn btn-primary">Registrovať</button>
                            <a href="<?= url("prihlasenie") ?>" class="btn btn-link">Prihlásenie</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php add_layout("footer") ?>