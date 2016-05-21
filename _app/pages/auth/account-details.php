<?php

$email = isset($_POST["email"]) ? $_POST["email"] : "";

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
                <div class="panel panel-primary">
                    <div class="panel-heading">
                        <h3 class="panel-title">Zmena emailu</h3>
                    </div>
                    <div class="panel-body">
                        <form class="form-horizontal" method="post">
                            <div class="form-group">
                                <label for="inputEmail" class="col-lg-2 control-label">Email</label>
                                <div class="col-lg-10">
                                    <input class="form-control" id="inputEmail" placeholder="Email" type="text" value="<?= plain($email) ?>">
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
                                    <input class="form-control" id="inputPasswordOld" placeholder="Aktuálne heslo" type="password" value="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputPasswordNew" class="col-lg-2 control-label">Nové heslo</label>
                                <div class="col-lg-10">
                                    <input class="form-control" id="inputPasswordNew" placeholder="Nové heslo" type="password" value="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputPasswordNewAgain" class="col-lg-2 control-label">Nové heslo znovu</label>
                                <div class="col-lg-10">
                                    <input class="form-control" id="inputPasswordNewAgain" placeholder="Nové heslo znovu" type="password" value="">
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
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-danger">
                    <div class="panel-heading">
                        <h3 class="panel-title">Deaktivácia účtu</h3>
                    </div>
                    <div class="panel-body">
                        <form class="form-horizontal" method="post">
                            <div class="form-group">
                                <label for="inputPasswordOld" class="col-lg-2 control-label">Aktuálne heslo</label>
                                <div class="col-lg-10">
                                    <input class="form-control" id="inputPasswordOld" placeholder="Aktuálne heslo" type="password" value="">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-lg-10 col-lg-offset-2">
                                    <button type="submit" class="btn btn-danger">Deaktivovať</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php add_layout("footer") ?>