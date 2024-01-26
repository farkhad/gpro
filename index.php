<?php

use SleekDB\Store;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/src/functions.php';

if (!class_exists('SleekDB\Store')) {
    echo "Navigate to <i>GPRO Home Server</i> folder and run console commands"
        ."<ol><li><pre>composer install</pre></li>"
        ."<li><pre>php sync.php</pre></li></ol>"
    ;
    exit;
}

$countryCodes = array_flip(getAllCountries());

$dbDir = __DIR__.DIRECTORY_SEPARATOR.DB_FOLDER_NAME;
$tz = new DateTimeZone(GPRO_TIMEZONE);
$dt = new DateTime('now', $tz);

$seasonCalendarStore = new Store("calendar", $dbDir, ['timeout' => false]);
$tracksStore = new Store("tracks", $dbDir, ['timeout' => false]);


$queryBuilder = $seasonCalendarStore
    ->createQueryBuilder()
    ->where([
        ["start", "<=", $dt->getTimestamp()],
        ["end", ">=", $dt->getTimestamp()]
    ])
    ->orderBy(['season' => 'desc'])
;
$seasonCalendar = $queryBuilder->join(
    function ($seasonCalendar) use ($tracksStore) {
        return $tracksStore->findBy(['id', 'IN', array_column($seasonCalendar['tracks'], 'track_id')]);
    },
    "tracks_details"
)->getQuery()->first();

$trackDetailsKeys = array_keys(
    array_column($seasonCalendar['tracks_details'], 'name', 'id')
);

$trackDetails = array_combine($trackDetailsKeys, $seasonCalendar['tracks_details']);

const MARKET_FILES_LIMIT = 5;

$seasonFolder = 'seasons' . DIRECTORY_SEPARATOR;
$marketFolder = 'market' . DIRECTORY_SEPARATOR;

$users = glob($seasonFolder . '*', GLOB_ONLYDIR);
$raceAnalysisFiles = $seasonRaces = $sponsors = [];
foreach ($users as $userDir) {
    if (!isset(ACCOUNTS[basename($userDir)])) {
        continue;
    }
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
$marketFiles = array_slice($marketFiles, 0, MARKET_FILES_LIMIT);

$marketFilesTechDirectors = glob($marketFolder . '[TD-]*.php');
rsort($marketFilesTechDirectors);
$marketFilesTechDirectors = array_slice($marketFilesTechDirectors, 0, MARKET_FILES_LIMIT);

$content = renderView(
    'index',
    compact(
        'raceAnalysisFiles',
        'seasonFolder',
        'marketFiles',
        'marketFilesTechDirectors',
        'seasonRaces',
        'sponsors',
        'seasonCalendar',
        'trackDetails',
        'countryCodes',
    )
);

echo renderView('layout', compact('content'));
