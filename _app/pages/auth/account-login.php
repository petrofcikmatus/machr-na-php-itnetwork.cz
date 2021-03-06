<?php

$am = new AuthModel();

if ($am->isLoggedIn()) redirect();

$email    = isset($_GET["email"]) ? $_GET["email"] : (isset($_POST["email"]) ? $_POST["email"] : "");
$password = isset($_POST["password"]) ? $_POST["password"] : "";

if (is_post_request()) {
    try {
        $am->doLogin($email, $password);
        add_message("Prihlásenie prebehlo úspešne.");
        redirect();
    } catch (Exception $e) {
        add_message($e->getMessage());
    }
}

add_layout("header", array("title" => "Prihlásenie"));

?>

    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="text-center">Prihlásenie</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <form class="form-horizontal" method="post">
                    <div class="form-group">
                        <label for="inputEmail" class="col-lg-2 control-label">Email</label>
                        <div class="col-lg-10">
                            <input class="form-control" id="inputEmail" placeholder="Email" type="text" name="email" value="<?= plain($email) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputPassword" class="col-lg-2 control-label">Heslo</label>
                        <div class="col-lg-10">
                            <input class="form-control" id="inputPassword" placeholder="Heslo" type="password" name="password" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-lg-10 col-lg-offset-2">
                            <button type="submit" class="btn btn-primary">Prihlásiť</button>
                            <a href="<?= url("registracia") ?>" class="btn btn-link">Registrácia</a>
                            <a href="<?= url("obnova-hesla") ?>" class="btn btn-link">Obnova hesla</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php add_layout("footer") ?>