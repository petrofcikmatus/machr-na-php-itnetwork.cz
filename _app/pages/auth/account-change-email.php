<?php

$am = new AuthModel();

if (!$am->isLoggedIn()) redirect("prihlasenie");

if (is_post_request()) {
    $email = isset($_POST["email"]) ? $_POST["email"] : "";

    try {
        $am->updateEmail($email);
        add_message("Email bol úspešne zmenený.");
        redirect("zmena-emailu");
    } catch (Exception $e) {
        add_message($e->getMessage());
    }
} else {
    $user  = $am->getUserData();
    $email = $user->email;
}

add_layout("header", array("title" => "Zmena emailu"));

?>

    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="text-center">Zmena emailu</h1>
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
                            <button type="submit" class="btn btn-primary" name="update_email">Uložiť</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php add_layout("footer") ?>