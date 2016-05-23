<?php

$am = new AuthModel();

if ($am->isLoggedIn()) redirect();

$email = isset($_POST["email"]) ? $_POST["email"] : "";

if (is_post_request()) {
    try {
        $am->sendRecoveryKey($email);
        add_message("Zaslanie obnovovacieho kľúča prebehlo úspešne.");
        redirect("obnova-hesla-krok-2?email=" . $email);
    } catch (Exception $e) {
        add_message($e->getMessage());
    }
}

add_layout("header", array("title" => "Obnova hesla - zaslanie obnovovacieho kľúča"));

?>

    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="text-center">Zaslanie obnovovacieho kľúča</h1>
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
                        <div class="col-lg-10 col-lg-offset-2">
                            <button type="submit" class="btn btn-primary">Zaslať obnovovací kľúč</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php add_layout("footer") ?>