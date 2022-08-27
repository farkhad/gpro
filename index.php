<?php
/**
 * Home page
 * Display race analysis, market files
 */
require_once __DIR__ . '/src/functions.php';

const MARKET_FILES_LIMIT = 5;

$seasonFolder = 'seasons' . DIRECTORY_SEPARATOR;
$marketFolder = 'market' . DIRECTORY_SEPARATOR;

$seasons = glob($seasonFolder . '*', GLOB_ONLYDIR);
rsort($seasons);
$seasons = array_slice($seasons, 0, 2);

$raceAnalysisFiles = [];
array_walk($seasons, function (&$season) use ($seasonFolder, &$raceAnalysisFiles) {
    $seasonRaceAnalysisFiles = glob($season . DIRECTORY_SEPARATOR . '*.html');
    $seasonRaceAnalysisFiles = array_filter($seasonRaceAnalysisFiles, 'isRaceAnalysisFile');

    usort($seasonRaceAnalysisFiles, function ($a, $b) {
        $pattern = '/S[0-9]+?R([0-9]+)/';
        preg_match($pattern, $a, $mA);
        preg_match($pattern, $b, $mB);

        return $mB[1] <=> $mA[1];
    });

    $season = str_replace($seasonFolder, '', $season);
    $raceAnalysisFiles[$season] = $seasonRaceAnalysisFiles;
});

$marketFiles = glob($marketFolder . '[!TD]*.php');
rsort($marketFiles);
$marketFiles = array_slice($marketFiles, 0, \MARKET_FILES_LIMIT);

$marketFilesTechDirectors = glob($marketFolder . '[TD-]*.php');
rsort($marketFilesTechDirectors);
$marketFilesTechDirectors = array_slice($marketFilesTechDirectors, 0, \MARKET_FILES_LIMIT);

$content = renderView(
    'index',
    compact(
        'raceAnalysisFiles',
        'seasonFolder',
        'marketFiles',
        'marketFilesTechDirectors'
    )
);

echo renderView('layout', compact('content'));
