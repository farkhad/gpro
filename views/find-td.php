<form method="GET">
    <?php if (empty($marketFileDefault)) : ?>
        <p class="text-danger">Market file not found.
            <a href="market.php">Download</a> latest tech directors market database file.
        </p>
    <?php endif; ?>
    <p>
        <small>Recommendation: <a href="market.php">download</a> latest market database file just before looking for the best tech director.</small>
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
            <th>Leadership<sub class="text-lowercase">min</sub></th>
            <th>R&D mechanics<sub class="text-lowercase">min</sub></th>
            <th>R&D electronics<sub class="text-lowercase">min</sub></th>
            <th>R&D aerodynamics<sub class="text-lowercase">min</sub></th>
        </tr>
        <tr>
            <td><input class="form-control form-control-sm" type="number" placeholder="90" name="OA" value="<?= $_OA ?>"></td>
            <td><input class="form-control form-control-sm" type="number" placeholder="0-250" name="LEA" value="<?= @$filters['LEA'] ?>"></td>
            <td><input class="form-control form-control-sm" type="number" placeholder="0-250" name="MEC" value="<?= @$filters['MEC'] ?>"></td>
            <td><input class="form-control form-control-sm" type="number" placeholder="0-250" name="ELE" value="<?= @$filters['ELE'] ?>"></td>
            <td><input class="form-control form-control-sm" type="number" placeholder="0-250" name="AER" value="<?= @$filters['AER'] ?>"></td>
        </tr>
        <tr class="text-uppercase">
            <th>Experience<sub class="text-lowercase">min</sub></th>
            <th>Pit coordination<sub class="text-lowercase">min</sub></th>
            <th>Motivation<sub class="text-lowercase">min</sub></th>
            <th colspan="2">Age<sup class="text-lowercase">max</sup></th>
        </tr>
        <tr>
            <td><input class="form-control form-control-sm" type="number" placeholder="0-400" name="EXP" value="<?= @$filters['EXP'] ?>"></td>
            <td><input class="form-control form-control-sm" type="number" placeholder="0-250" name="PIT" value="<?= @$filters['PIT'] ?>"></td>
            <td><input class="form-control form-control-sm" type="number" placeholder="0-250" name="MOT" value="<?= @$filters['MOT'] ?>"></td>
            <td><input class="form-control form-control-sm" type="number" placeholder="0-99" name="AGE" value="<?= @$filters['AGE'] ?>"></td>
            <td><button type="submit" class="btn btn-primary btn-sm w-50">Find</button></td>
        </tr>
    </table>
</form>
<p>Total: <?= count($tds) ?></p>
<table class="d-none" id="table" data-search="true" data-show-columns="true" data-sortable="true">
    <thead>
        <tr class="text-uppercase">
            <th data-field="NAME" data-sortable="true" data-formatter="nameFormatter">Name</th>
            <th data-field="OA" data-sortable="true">OA</th>
            <th data-field="LEA" data-sortable="true">LEA</th>
            <th data-field="MEC" data-sortable="true">MEC</th>
            <th data-field="ELE" data-sortable="true">ELE</th>
            <th data-field="AER" data-sortable="true">AER</th>
            <th data-field="EXP" data-sortable="true">EXP</th>
            <th data-field="PIT" data-sortable="true">PIT</th>
            <th data-field="MOT" data-sortable="true">MOT</th>
            <th data-field="AGE" data-sortable="true">AGE</th>
            <th data-field="FEE" data-sortable="true" data-formatter="feeFormatter">Fee</th>
            <th data-field="SAL" data-formatter="salFormatter" data-sortable="true">Sal</th>
            <th data-field="OFF" data-sortable="true">Offers</th>
        </tr>
    </thead>
</table>

<script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
<script src="https://unpkg.com/bootstrap-table@1.22.2/dist/bootstrap-table.min.js"></script>
<script>
    var $table = $('#table');
    $table.bootstrapTable({
        data: <?= json_encode($tds) ?>,
        sortable: true
    });
    $table.toggleClass('d-none');

    function nameFormatter(value, row) {
        return '<a href="<?= \BASE_TD_URI ?>?ID=' + row.ID + '" target="_blank">' +
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
</script>
