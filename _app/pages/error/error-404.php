<?php add_layout("header", array("title" => "Stránka nebola nájdená")) ?>

    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-info">
                    <div class="panel-heading">
                        <h3 class="panel-title">Chyba 404</h3>
                    </div>
                    <div class="panel-body">
                        Stránka nebola nájdená. Pravdepodobne máte preklep v url adrese alebo ste klikli na nefunkčný odkaz.
                        <a href="<?= url() ?>">Úvodná stránka</a>.
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php add_layout("footer") ?>