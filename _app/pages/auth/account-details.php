<?php

$am = new AuthModel();

if (!$am->isLoggedIn()) redirect("prihlasenie");

$email = isset($_POST["email"]) ? $_POST["email"] : "";

if (is_post_request()) {
    if (isset($_POST["update_password"])) {

        $password_old       = isset($_POST["password_old"]) ? $_POST["password_old"] : "";
        $password_new       = isset($_POST["password_new"]) ? $_POST["password_new"] : "";
        $password_new_again = isset($_POST["password_new_again"]) ? $_POST["password_new_again"] : "";

        try {
            $am->updateEmail($email);
            add_message("Heslo bolo úspešne zmenené.");
            redirect("moj-ucet");
        } catch (Exception $e) {
            add_message($e->getMessage());
        }
    } elseif (isset($_POST["update_email"])) {
        try {
            $am->updateEmail($email);
            add_message("Email bol úspešne zmenený.");
            redirect("moj-ucet");
        } catch (Exception $e) {
            add_message($e->getMessage());
        }
    } else {
        add_message("WUT?!");
        redirect("moj-ucet");
    }
}

$user = $am->getUserData();

add_layout("header", array("title" => "Môj účet"));

?>

    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="text-center">Môj účet</h1>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">Informácie o účte</h3>
                    </div>
                    <div class="panel-body">
                        <table class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Key</th>
                                <th>Value</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>id</td>
                                <td><?= $user->id ?></td>
                            </tr>
                            <tr>
                                <td>email</td>
                                <td><?= $user->email ?></td>
                            </tr>
                            <tr>
                                <td>created_at</td>
                                <td><?= $user->created_at ?></td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Zmena emailu</h3>
                    </div>
                    <div class="panel-body">
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
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Zmena hesla</h3>
                    </div>
                    <div class="panel-body">
                        <form class="form-horizontal" method="post">
                            <div class="form-group">
                                <label for="inputPasswordOld" class="col-lg-2 control-label">Aktuálne heslo</label>
                                <div class="col-lg-10">
                                    <input class="form-control" id="inputPasswordOld" placeholder="Aktuálne heslo" type="password" name="password_old" value="">
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
                                    <button type="submit" class="btn btn-primary" name="update_password">Uložiť</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php add_layout("footer") ?>