<?php

$am = new AuthModel();

if (!$am->isLoggedIn()) redirect("prihlasenie");

if (is_post_request()) {
    $password_old       = isset($_POST["password_old"]) ? $_POST["password_old"] : "";
    $password_new       = isset($_POST["password_new"]) ? $_POST["password_new"] : "";
    $password_new_again = isset($_POST["password_new_again"]) ? $_POST["password_new_again"] : "";

    try {
        $am->updatePassword($password_old, $password_new, $password_new_again);
        add_message("Heslo bolo úspešne zmenené.");
        redirect("zmena-hesla");
    } catch (Exception $e) {
        add_message($e->getMessage());
    }
}

add_layout("header", array("title" => "Zmena hesla"));

?>

    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="text-center">Zmena hesla</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <form class="form-horizontal" method="post">
                    <div class="form-group">
                        <label for="inputPassword" class="col-lg-2 control-label">Aktuálne heslo</label>
                        <div class="col-lg-10">
                            <input class="form-control" id="inputPassword" placeholder="Aktuálne heslo" type="password" name="password_old" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputPasswordNew" class="col-lg-2 control-label">Nové heslo</label>
                        <div class="col-lg-10">
                            <input class="form-control" id="inputPasswordNew" placeholder="Nové heslo" type="password" name="password_new" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputPasswordNewAgain" class="col-lg-2 control-label">Nové heslo znovu</label>
                        <div class="col-lg-10">
                            <input class="form-control" id="inputPasswordNewAgain" placeholder="Nové heslo znovu" type="password" name="password_new_again" value="">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-lg-10 col-lg-offset-2">
                            <button type="submit" class="btn btn-primary">Uložiť</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

<?php add_layout("footer") ?>