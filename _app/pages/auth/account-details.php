<?php

$am = new AuthModel();

if (!$am->isLoggedIn()) redirect("prihlasenie");

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
                    <tr>
                        <td>password_salt</td>
                        <td><?= $user->password_salt ?></td>
                    </tr>
                    <tr>
                        <td>password_hash</td>
                        <td><?= $user->password_hash ?></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<?php add_layout("footer") ?>