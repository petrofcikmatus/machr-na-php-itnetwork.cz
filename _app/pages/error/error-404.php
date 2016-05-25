<?php add_layout("header", array("title" => "Stránka nebola nájdená")) ?>

    <div class="container">
        <div class="jumbotron">
            <h1>A jeje! Chyba 404 :'(</h1>
            <h2>To čo hľadáš nenájdeš! Takáto stránka totiž zeexistuje.</h2>
            <p>Pravdepodobne máš preklep v url adrese alebo ste klikli na nefunkčný odkaz.</p>
            <p><a class="btn btn-primary" href="<?= url() ?>">Úvodná stránka</a></p>
        </div>
    </div>

<?php add_layout("footer") ?>