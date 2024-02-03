<form method="GET">
    <?php if (empty($marketFileDefault)) : ?>
        <p class="text-danger">Market file not found.
            <a href="market.php">Download</a> latest drivers market database file.
        </p>
    <?php endif; ?>
    <p>
        <small>Recommendation: <a href="market.php">download</a> latest market database file just before looking for the best driver.</small>
    </p>
    <div class="w-25 mb-3">
        <label for="market">Market</label>
        <div class="row">
            <div class="col"><select class="form-select form-select-sm" id="market" name="market">
                    <?php
                    foreach ($marketFiles as $marketFile) :
                    ?>
                        <option value="<?= $marketFile ?>" <?= $marketFile === $marketFileDefault ? 'selected' : '' ?>><?= str_replace('.php', '', $marketFile) ?></option>
                    <?php
                    endforeach;
                    ?>
                </select>
            </div>
            <div class="col">
                <button class="btn btn-primary btn-sm">Select</button>
            </div>
        </div>
    </div>
    <table class="table table-striped">
        <tr class="text-uppercase">
            <th>OA<sup class="text-lowercase">max</sup></th>
            <th>Concentration<sub class="text-lowercase">min</sub></th>
            <th>Talent<sub class="text-lowercase">min</sub></th>
            <th>Aggressiveness<sub class="text-lowercase">min</sub></th>
            <th>Experience<sub class="text-lowercase">min</sub></th>
            <th>Technical Insight<sub class="text-lowercase">min</sub></th>
            <th>Stamina<sub class="text-lowercase">min</sub></th>
        </tr>
        <tr>
            <td><input class="form-control form-control-sm" type="number" placeholder="85" name="OA" value="<?= $_OA ?>"></td>
            <td><input class="form-control form-control-sm" type="number" placeholder="0-250" name="CON" value="<?= @$filters['CON'] ?>"></td>
            <td><input class="form-control form-control-sm" type="number" placeholder="0-250" name="TAL" value="<?= @$filters['TAL'] ?>"></td>
            <td><input class="form-control form-control-sm" type="number" placeholder="0-250" name="AGG" value="<?= @$filters['AGG'] ?>"></td>
            <td><input class="form-control form-control-sm" type="number" placeholder="0-250" name="EXP" value="<?= @$filters['EXP'] ?>"></td>
            <td><input class="form-control form-control-sm" type="number" placeholder="0-250" name="TEI" value="<?= @$filters['TEI'] ?>"></td>
            <td><input class="form-control form-control-sm" type="number" placeholder="0-250" name="STA" value="<?= @$filters['STA'] ?>"></td>
        </tr>
        <tr class="text-uppercase">
            <th>Charisma<sub class="text-lowercase">min</sub></th>
            <th>Motivation<sub class="text-lowercase">min</sub></th>
            <th>Reputation<sub class="text-lowercase">min</sub></th>
            <th>Weight<sup class="text-lowercase">max</sup></th>
            <th>Age<sup class="text-lowercase">max</sup></th>
            <th colspan="2" title="Search for Drivers w these Fav Tracks">Favourite Track ID(s)</th>
        </tr>
        <tr>
            <td><input class="form-control form-control-sm" type="number" placeholder="0-250" name="CHA" value="<?= @$filters['CHA'] ?>"></td>
            <td><input class="form-control form-control-sm" type="number" placeholder="0-250" name="MOT" value="<?= @$filters['MOT'] ?>"></td>
            <td><input class="form-control form-control-sm" type="number" placeholder="0-250" name="REP" value="<?= @$filters['REP'] ?>"></td>
            <td><input class="form-control form-control-sm" type="number" placeholder="0-150" name="WEI" value="<?= @$filters['WEI'] ?>"></td>
            <td><input class="form-control form-control-sm" type="number" placeholder="0-99" name="AGE" value="<?= @$filters['AGE'] ?>"></td>
            <td><input class="form-control form-control-sm" type="text" placeholder="52, 49, 10" name="FAV" value="<?= @$_GET['FAV'] ?>"></td>
            <td><button type="submit" class="btn btn-primary btn-sm w-100">Find</button></td>
        </tr>
    </table>
</form>
<p>Total: <?= count($drivers) ?></p>
<table class="d-none" id="table" data-search="true" data-show-columns="true" data-sortable="true" data-buttons-align="left" data-search-align="left">
    <thead>
        <tr class="text-uppercase">
            <th data-field="NAME" data-sortable="true" data-formatter="nameFormatter">Name</th>
            <th data-field="OA" data-sortable="true">OA</th>
            <th data-field="CON" data-sortable="true">Con</th>
            <th data-field="TAL" data-sortable="true">Tal</th>
            <th data-field="AGG" data-sortable="true">Agg</th>
            <th data-field="EXP" data-sortable="true">Exp</th>
            <th data-field="TEI" data-sortable="true">TEI</th>
            <th data-field="STA" data-sortable="true">Sta</th>
            <th data-field="CHA" data-sortable="true">Cha</th>
            <th data-field="MOT" data-sortable="true">Mot</th>
            <th data-field="REP" data-sortable="true">Rep</th>
            <th data-field="WEI" data-sortable="true">Wei</th>
            <th data-field="AGE" data-sortable="true">Age</th>
            <th data-field="FEE" data-sortable="true" data-formatter="feeFormatter">Fee</th>
            <th data-field="SAL" data-formatter="salFormatter" data-sortable="true">Sal</th>
            <th data-field="OFF" data-sortable="true">Offers</th>
            <th data-field="FAV" data-formatter="favFormatter">Favs</th>
        </tr>
    </thead>
</table>

<script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
<script src="https://unpkg.com/bootstrap-table@1.22.2/dist/bootstrap-table.min.js"></script>
<script>
    function nameFormatter(value, row) {
        return '<a href="<?= \BASE_DRIVER_URI ?>?ID=' + row.ID + '" target="_blank">' +
            row.NAME + '</a>';
    }

    function favFormatter(value, row) {
        return row.FAV.length;
    }

    function formatNumber(n) {
        return new Intl.NumberFormat('de-DE').format(n)
    }

    function salFormatter(value) {
        return formatNumber(value);
    }

    function feeFormatter(value) {
        return formatNumber(value);
    }

    var $table = $('#table');

    $table.bootstrapTable({
        data: <?= json_encode($drivers) ?>,
        sortable: true
    });

    $table.toggleClass('d-none');
</script>
