<?php

/**
 *
 * Find best driver on the market
 */
require_once __DIR__ . '/src/functions.php';

$title = 'Find Best Driver';
$marketFolder = 'market' . DIRECTORY_SEPARATOR;

$marketFiles = glob($marketFolder . '[!TD]*.php');
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
const BASE_DRIVER_URI = 'https://www.gpro.net/DriverProfile.asp';

$filters = [];

foreach ($_GET as $key => $val) {
    if (empty($val)) {
        continue;
    }
    $filters[$key] = (int) $val;
}

if (!empty($filters) && !empty($marketFileDefault)) {
    $drivers = require $marketFolder . $marketFileDefault . '.php';
} else {
    $drivers = ['drivers' => []];
}

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
        if (in_array($key, ['AGE', 'WEI'])) {
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

$content = renderView(
    'find',
    compact(
        'marketFileDefault',
        'marketFiles',
        '_OA',
        'filters',
        'drivers'
    )
);
echo renderView('layout', compact('content', 'title'));
