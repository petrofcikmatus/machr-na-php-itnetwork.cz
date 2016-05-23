<?php

$am = new AuthModel();

if ($am->isLoggedIn()) redirect();

$email = isset($_GET["email"]) ? $_GET["email"] : (isset($_POST["email"]) ? $_POST["email"] : "");
$key   = isset($_GET["key"]) ? $_GET["key"] : (isset($_POST["key"]) ? $_POST["key"] : "");

if (is_post_request()) {
    try {
        $am->useActivationKey($email, $key);
        add_message("Aktivácia účtu prebehla úspešne.");
        redirect("prihlasenie?email=" . $email);
    } catch (Exception $e) {
        add_message($e->getMessage());
    }
}

add_layout("header", array("title" => "Aktivácia účtu"));

?>

    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="text-center">Aktivácia účtu</h1>
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
                        <label for="inputKey" class="col-lg-2 control-label">Aktivačný kľúč</label>
                        <div class="col-lg-10">
                            <input class="form-control" id="inputKey" placeholder="Aktivačný kľúč" type="text" name="key" value="<?= plain($key) ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-lg-10 col-lg-offset-2">
                            <button type="submit" class="btn btn-primary">Aktivovať</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php add_layout("footer") ?>