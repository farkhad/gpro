<?php

$marketFolder = 'market' . DIRECTORY_SEPARATOR;

$marketFiles = glob($marketFolder . '*.php');
rsort($marketFiles);
$marketFiles = array_slice($marketFiles, 0, 3);
array_walk($marketFiles, function (&$element) {
    $element = str_replace(
        ['market' . DIRECTORY_SEPARATOR, '.php'],
        ['', ''],
        $element
    );
});

if (!count($marketFiles)) {
    echo 'Market file not found. <a href="market.php">Download</a> latest drivers market database file.';
    exit;
}

$marketFileDefault = $marketFiles[0];
if (!empty($_GET['market'])) {
    $marketFileDefault = $_GET['market'];
}
unset($_GET['market']);

$drivers = require $marketFolder . $marketFileDefault . '.php';

$_OA = 85;
if (!empty($_GET['OA'])) {
    $_OA = (int) $_GET['OA'];
    unset($_GET['OA']);
}

// Desirable Favourite Tracks
$FTs = [];
if (!empty($_GET['FAV'])) {
    $FTs = explode(',', $_GET['FAV']);

    array_walk($FTs, function (&$element) {
        $element = trim($element);
        $element = (int) $element;
    });
}

const ASC = 'ASC';
const DESC = 'DESC';
const BASE_DRIVER_URI = 'https://www.gpro.net/gb/DriverProfile.asp';

$filters = [
    'CON' => 100,
    'TAL' => 100,
    'EXP' => 20,
    'STA' => 0,
];

foreach ($_GET as $key => $val) {
    if (empty($val)) {
        continue;
    }
    $filters[$key] = (int) $val;
};

// Supported 3 levels of sorting
define('SORTING', [
    0 => [
        'attr' => 'CON',
        'direction' => \DESC
    ],
    // 1 => [
    // 	'attr' => 'EXP',
    // 	'direction' => \DESC,
    // ],
    // 2 => [
    // 	'attr' => 'TAL',
    // 	'direction' => \DESC
    // ]
]);

$driversFiltered = [];
foreach ($drivers['drivers'] as $driver) {
    if ($driver['OA'] > $_OA) {
        continue;
    }

    foreach ($filters as $key => $min) {
        if ($key === 'AGE') {
            if ($driver[$key] > $min) {
                continue 2;
            }
            continue;
        }

        if (isset($driver[$key]) && $driver[$key] < $min) {
            continue 2;
        }
    }

    if (count($FTs)) {
        $FTsFound = array_intersect($FTs, $driver['FAV']);
        if (!count($FTsFound)) {
            continue;
        }
    }

    $driversFiltered[] = $driver;
}
unset($drivers);

function sortDrivers($drivers, $sorting)
{
    // First, Group By
    $driversGrouped = [];
    foreach ($drivers as $driver) {
        $driversGrouped[$driver[$sorting['attr']]][] = $driver;
    }
    unset($drivers);

    // Second, Order By
    if ($sorting['direction'] === \ASC) {
        ksort($driversGrouped);
    } else {
        krsort($driversGrouped);
    }
    return $driversGrouped;
}

if (isset(\SORTING[0])) {
    $driversGrouped = sortDrivers($driversFiltered, \SORTING[0]);
    unset($driversFiltered);
} else {
    $driversGrouped = &$driversFiltered;
}

if (isset(\SORTING[1])) {
    foreach ($driversGrouped as $sortedBy => $drivers) {
        $driversGrouped[$sortedBy] = sortDrivers($drivers, \SORTING[1]);
    }
}

if (isset(\SORTING[2])) {
    foreach ($driversGrouped as $sortedBy => $driversGrouped2) {
        foreach ($driversGrouped2 as $sortedBy2 => $drivers) {
            $driversGrouped2[$sortedBy2] = sortDrivers($drivers, \SORTING[2]);
        }
        $driversGrouped[$sortedBy] = $driversGrouped2;
    }
}

$sortingLevels = count(\SORTING);
$drivers = [];
foreach ($driversGrouped as $sortedBy => $driversLevel1) {
    switch ($sortingLevels) {
        case 1:
            $drivers = array_merge($drivers, $driversLevel1);
            break;

        case 2:
            foreach ($driversLevel1 as $sortedBy2 => $driversLevel2) {
                $drivers = array_merge($drivers, $driversLevel2);
            }
            break;

        case 3:
            foreach ($driversLevel1 as $sortedBy2 => $driversLevel2) {
                foreach ($driversLevel2 as $sortedBy3 => $driversLevel3) {
                    $drivers = array_merge($drivers, $driversLevel3);
                }
            }
            break;

        default:
            $drivers = $driversGrouped;
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
    <title>Market</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.20.2/dist/bootstrap-table.min.css">
</head>

<body class="m-5">
    <h1 class="mb-3 text-center">Find Best Driver On The Market!</h1>
    <form method="GET">
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
        <table>
            <tr class="text-uppercase">
                <th>OA<sup class="text-lowercase">max</sup></th>
                <th>Concentration<sub class="text-lowercase">min</sub></th>
                <th>Talent<sub class="text-lowercase">min</sub></th>
                <th>Experience<sub class="text-lowercase">min</sub></th>
                <th>Stamina<sub class="text-lowercase">min</sub></th>
                <th>Age<sup class="text-lowercase">max</sup></th>
                <th colspan="2" title="Search for Drivers w these Fav Tracks">Favourite Track ID(s)</th>
            </tr>
            <tr>
                <td><input class="form-control form-control-sm" type="number" placeholder="85" name="OA"
                    value="<?= $_OA ?>"></td>
                <td><input class="form-control form-control-sm" type="number" placeholder="100" name="CON"
                    value="<?= @$filters['CON'] ?>"></td>
                <td><input class="form-control form-control-sm" type="number" placeholder="100" name="TAL"
                    value="<?= @$filters['TAL'] ?>"></td>
                <td><input class="form-control form-control-sm" type="number" placeholder="20" name="EXP"
                    value="<?= @$filters['EXP'] ?>"></td>
                <td><input class="form-control form-control-sm" type="number" placeholder="50" name="STA"
                    value="<?= @$filters['STA'] ?>"></td>
                <td><input class="form-control form-control-sm" type="number" placeholder="34" name="AGE"
                    value="<?= @$filters['AGE'] ?>"></td>
                <td><input class="form-control form-control-sm" type="text" placeholder="52, 49, 10" name="FAV"
                    value="<?= @$_GET['FAV'] ?>"></td>
                <td><button class="btn btn-primary btn-sm">Find</button></td>
            </tr>
        </table>
    </form>
    <table class="d-none" id="table" data-toggle="table" data-search="true" data-show-columns="true" data-sortable="true" data-buttons-align="left" data-search-align="left">
        <thead>
            <tr class="text-uppercase">
                <th data-field="NAME" data-formatter="nameFormatter">Name</th>
                <th data-field="ID" data-formatter="ranking">Ranking</th>
                <th data-field="OA" data-sortable="true">OA</th>
                <th data-field="CON" data-sortable="true">Con</th>
                <th data-field="TAL" data-sortable="true">Tal</th>
                <th data-field="AGG">Agg</th>
                <th data-field="EXP" data-sortable="true">Exp</th>
                <th data-field="TEI">TI</th>
                <th data-field="STA" data-sortable="true">Sta</th>
                <th data-field="CHA" data-sortable="true">Cha</th>
                <th data-field="MOT">Mot</th>
                <th data-field="REP">Rep</th>
                <th data-field="WEI">Wei</th>
                <th data-field="AGE" data-sortable="true">Age</th>
                <th data-field="FEE" data-formatter="feeFormatter">Fee</th>
                <th data-field="SAL" data-formatter="salFormatter" data-sortable="true">Sal</th>
                <th data-field="OFF" data-sortable="true">Offers</th>
                <th data-field="FAV" data-formatter="favFormatter">Favs</th>
            </tr>
        </thead>
    </table>

    <script src="https://cdn.jsdelivr.net/npm/jquery/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/bootstrap-table@1.20.2/dist/bootstrap-table.min.js"></script>
    <script>
        var $table = $('#table');
        $table.bootstrapTable({
            data: <?= json_encode($drivers) ?>,
            sortable: true
        });
        $table.toggleClass('d-none');

        function nameFormatter(index, row) {
            return '<a href="<?= \BASE_DRIVER_URI ?>?ID=' + row.ID + '" target="_blank">' +
                row.NAME + '</a>';
        }

        function favFormatter(index, row) {
            return row.FAV.length;
        }

        function formatNumber(n) {
            return new Intl.NumberFormat('de-DE').format(n)
        }

        function salFormatter(index, row) {
            return formatNumber(row.SAL);
        }

        function feeFormatter(index, row) {
            return formatNumber(row.FEE);
        }

        function ranking(index, row) {
            return Number(row.CON + row.TAL + 0.1 * row.AGG + 1.5 * row.EXP + 2 * row.STA - row.WEI).toFixed(1);
        }
    </script>
</body>

</html>