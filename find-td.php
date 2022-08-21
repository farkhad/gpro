<?php

/**
 *
 * Find best tech director on the market
 */
$marketFolder = 'market' . DIRECTORY_SEPARATOR;

$marketFiles = glob($marketFolder . '[TD-]*.php');
rsort($marketFiles);
$marketFiles = array_slice($marketFiles, 0, 3);
array_walk($marketFiles, function (&$element) use ($marketFolder) {
    $element = str_replace(
        [$marketFolder, '.php'],
        ['', ''],
        $element
    );
});

if (count($marketFiles)) {
    $marketFileDefault = $marketFiles[0];
    if (!empty($_GET['market'])) {
        $marketFileDefault = $_GET['market'];
    }
    unset($_GET['market']);
}

$_OA = 90;
if (!empty($_GET['OA'])) {
    $_OA = (int) $_GET['OA'];
    unset($_GET['OA']);
}

const ASC = 'ASC';
const DESC = 'DESC';
const BASE_TD_URI = 'https://www.gpro.net/TechDProfile.asp';

$filters = [];

foreach ($_GET as $key => $val) {
    if (empty($val)) {
        continue;
    }
    $filters[$key] = (int) $val;
}

if (!empty($filters) && !empty($marketFileDefault)) {
    $tds = require $marketFolder . $marketFileDefault . '.php';
} else {
    $tds = ['tds' => []];
}

// Supported 3 levels of sorting
define('SORTING', [
    0 => [
        'attr' => 'PIT',
        'direction' => \DESC
    ],
]);

$tdsFiltered = [];
foreach ($tds['tds'] as $td) {
    if ($td['OA'] > $_OA) {
        continue;
    }

    foreach ($filters as $key => $min) {
        if (in_array($key, ['AGE'])) {
            if ($td[$key] > $min) {
                continue 2;
            }
            continue;
        }

        if (isset($td[$key]) && $td[$key] < $min) {
            continue 2;
        }
    }

    $tdsFiltered[] = $td;
}
unset($tds);

function sortTechDirectors($tds, $sorting)
{
    // First, Group By
    $tdsGrouped = [];
    foreach ($tds as $td) {
        $tdsGrouped[$td[$sorting['attr']]][] = $td;
    }
    unset($tds);

    // Second, Order By
    if ($sorting['direction'] === \ASC) {
        ksort($tdsGrouped);
    } else {
        krsort($tdsGrouped);
    }
    return $tdsGrouped;
}

if (isset(\SORTING[0])) {
    $tdsGrouped = sortTechDirectors($tdsFiltered, \SORTING[0]);
    unset($tdsFiltered);
} else {
    $tdsGrouped = &$tdsFiltered;
}

if (isset(\SORTING[1])) {
    foreach ($tdsGrouped as $sortedBy => $tds) {
        $tdsGrouped[$sortedBy] = sortTechDirectors($tds, \SORTING[1]);
    }
}

if (isset(\SORTING[2])) {
    foreach ($tdsGrouped as $sortedBy => $tdsGrouped2) {
        foreach ($tdsGrouped2 as $sortedBy2 => $tds) {
            $tdsGrouped2[$sortedBy2] = sortTechDirectors($tds, \SORTING[2]);
        }
        $tdsGrouped[$sortedBy] = $tdsGrouped2;
    }
}

$sortingLevels = count(\SORTING);
$tds = [];
foreach ($tdsGrouped as $sortedBy => $tdsLevel1) {
    switch ($sortingLevels) {
        case 1:
            $tds = array_merge($tds, $tdsLevel1);
            break;

        case 2:
            foreach ($tdsLevel1 as $sortedBy2 => $tdsLevel2) {
                $tds = array_merge($tds, $tdsLevel2);
            }
            break;

        case 3:
            foreach ($tdsLevel1 as $sortedBy2 => $tdsLevel2) {
                foreach ($tdsLevel2 as $sortedBy3 => $tdsLevel3) {
                    $tds = array_merge($tds, $tdsLevel3);
                }
            }
            break;

        default:
            $tds = $tdsGrouped;
            break 2;
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Tech Directors Market</title>
    <link rel="shortcut icon" href="img/favicon.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.20.2/dist/bootstrap-table.min.css">
    <script async src="https://kit.fontawesome.com/f711a4bfbd.js" crossorigin="anonymous"></script>
</head>

<body class="m-5">
<?php
include 'nav.php';
?>
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
                <td><input class="form-control form-control-sm" type="number" placeholder="90" name="OA"
                    value="<?= $_OA ?>"></td>
                <td><input class="form-control form-control-sm" type="number" placeholder="0-250" name="LEA"
                    value="<?= @$filters['LEA'] ?>"></td>
                <td><input class="form-control form-control-sm" type="number" placeholder="0-250" name="MEC"
                    value="<?= @$filters['MEC'] ?>"></td>
                <td><input class="form-control form-control-sm" type="number" placeholder="0-250" name="ELE"
                    value="<?= @$filters['ELE'] ?>"></td>
                <td><input class="form-control form-control-sm" type="number" placeholder="0-250" name="AER"
                    value="<?= @$filters['AER'] ?>"></td>
            </tr>
            <tr class="text-uppercase">
                <th>Experience<sub class="text-lowercase">min</sub></th>
                <th>Pit coordination<sub class="text-lowercase">min</sub></th>
                <th>Motivation<sub class="text-lowercase">min</sub></th>
                <th colspan="2">Age<sup class="text-lowercase">max</sup></th>
            </tr>
            <tr>
                <td><input class="form-control form-control-sm" type="number" placeholder="0-400" name="EXP"
                    value="<?= @$filters['EXP'] ?>"></td>
                <td><input class="form-control form-control-sm" type="number" placeholder="0-250" name="PIT"
                    value="<?= @$filters['PIT'] ?>"></td>
                <td><input class="form-control form-control-sm" type="number" placeholder="0-250" name="MOT"
                    value="<?= @$filters['MOT'] ?>"></td>
                <td><input class="form-control form-control-sm" type="number" placeholder="0-99" name="AGE"
                    value="<?= @$filters['AGE'] ?>"></td>
                <td><button type="submit" class="btn btn-primary btn-sm w-50">Find</button></td>
            </tr>
        </table>
    </form>
    <p>Total: <?= count($tds) ?></p>
    <table class="d-none" id="table" data-toggle="table" data-search="true" data-show-columns="true" data-sortable="true">
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/bootstrap-table@1.20.2/dist/bootstrap-table.min.js"></script>
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
</body>

</html>
