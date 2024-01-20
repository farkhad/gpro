<?php

/**
 * Home page
 * Display race analysis, market files
 */
require_once __DIR__ . '/src/functions.php';

const MARKET_FILES_LIMIT = 5;

$seasonFolder = 'seasons' . DIRECTORY_SEPARATOR;
$marketFolder = 'market' . DIRECTORY_SEPARATOR;

$users = glob($seasonFolder . '*', GLOB_ONLYDIR);
$raceAnalysisFiles = $seasonRaces = $sponsors = [];
foreach ($users as $userDir) {
    $seasons = [];
    $seasons = glob($userDir . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR);
    rsort($seasons);
    $seasons = array_slice($seasons, 0, 2);

    array_walk($seasons, function (&$season) use ($userDir, &$raceAnalysisFiles, &$seasonRaces, &$sponsors) {
        $seasonRaceAnalysisFiles = glob($season . DIRECTORY_SEPARATOR . '*.html');
        $seasonRaceAnalysisFiles = array_filter($seasonRaceAnalysisFiles, 'isRaceAnalysisFile');

        usort($seasonRaceAnalysisFiles, 'sortRaceFiles');

        if (empty($sponsors)) {
            $seasonJsonFiles = glob($season . DIRECTORY_SEPARATOR . '*.json');
            usort($seasonJsonFiles, 'sortRaceFiles');

            foreach ($seasonJsonFiles as $seasonJsonFile) {
                $seasonRace = json_decode(file_get_contents($seasonJsonFile), true);

                foreach ($seasonRace['sponsors']['negotiations'] as $sponsor) {
                    $sponsor['race'] = str_replace('.json', '', basename($seasonJsonFile));
                    $seasonRaces[$sponsor['race']] = $seasonRace;

                    $sponsors[$userDir][$sponsor['name']][$sponsor['race']] = $sponsor;
                }
            }
        }

        $season = str_replace($userDir . DIRECTORY_SEPARATOR, '', $season);
        $raceAnalysisFiles[$userDir][$season] = $seasonRaceAnalysisFiles;
    });
}

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
        'marketFilesTechDirectors',
        'seasonRaces',
        'sponsors',
    )
);

echo renderView('layout', compact('content'));
