<?php

/**
 *
 * Find best tech director on the market
 */
require_once __DIR__ . '/src/functions.php';
$title = 'Find Tech Director';

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

$content = renderView(
    'find-td',
    compact(
        'marketFileDefault',
        'marketFiles',
        '_OA',
        'filters',
        'tds'
    )
);
echo renderView('layout', compact('content', 'title'));
