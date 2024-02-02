<?php

use Gpro\HomeParser;
use Gpro\TrackDetailsParser;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\SessionCookieJar;
use GuzzleHttp\RequestOptions;
use SleekDB\Store;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/src/functions.php';

$dbDir = __DIR__.DIRECTORY_SEPARATOR.DB_FOLDER_NAME;
$start = time();

session_start();

$client = new Client(['base_uri' => GPRO_URL, 'cookies' => new SessionCookieJar('gpro', true)]);

try {
    $homeHtml = $client->post('Login.asp?Redirect=gpro.asp', [
        'form_params' => [
            'textLogin' => USERNAME,
            'textPassword' => PASSWORD,
            'token' => HASH,
            'Logon' => 'Login',
            'LogonFake' => 'Login',
        ],
        'allow_redirects' => true,
        [
            RequestOptions::HEADERS => [
                'User-Agent' => GPRO_UA
            ],
        ],
    ])->getBody()->getContents();
} catch (Throwable $e) {
    echo '<small class="text-muted">Unable to retrieve data from gpro.net</small>'.PHP_EOL;
    echo '<!--'.$e->getMessage().'-->'.PHP_EOL;
    exit;
}

$homeParser = new HomeParser($homeHtml);
$season = $homeParser->season;

$calendarStore = new Store('calendar', $dbDir, ['timeout' => false]);
$calendar = $calendarStore->findOneBy(['season', '=', $season]);

$nextRaces = array_filter($calendar['tracks'], fn ($value) => is_null($value['winner_id']));
$nextRace = array_shift($nextRaces);

try {
    $trackDetailsHtml = $client->get(
    'TrackDetails.asp',
    [
        RequestOptions::HEADERS => [
            'User-Agent' => GPRO_UA
        ],
        RequestOptions::QUERY => [
            'id' => $nextRace['track_id'],
        ],
    ]
    )->getBody();
} catch (Throwable $e) {
    echo '<small class="text-muted">Unable to retrieve data from gpro.net</small>'.PHP_EOL;
    echo '<!--'.$e->getMessage().'-->'.PHP_EOL;
    exit;
}

$trackDetailsParser = new TrackDetailsParser($trackDetailsHtml);

$absRaceTimes = array_column($trackDetailsParser->history, 'abs_time');
sort($absRaceTimes);
$nbAbsRaceTimes = count($absRaceTimes);

$middleValue = floor(($nbAbsRaceTimes-1)/2);
if ($nbAbsRaceTimes % 2) {
    $medianAbsRaceTime = $absRaceTimes[$middleValue];
} else {
    $low = $absRaceTimes[$middleValue];
    $high = $absRaceTimes[$middleValue+1];
    $medianAbsRaceTime = (($low+$high)/2);
}

$medianHours = floor($medianAbsRaceTime/3600);
$medianMinutes = round(($medianAbsRaceTime-$medianHours*3600)/60);

$trackHistory = array_reverse($trackDetailsParser->history, true);
$seasonFolder = 'seasons'.DIRECTORY_SEPARATOR.FOLDER_NAME.DIRECTORY_SEPARATOR;

$filteredHistory = [];
foreach ($trackHistory as $i => $history) {
    $season = $history['season'];
    $race = $history['race'];
    $time = $history['time'];
    $raceId = 'S'.$season.'R'.$race;
    $raceJsonFile = $seasonFolder.$season.DIRECTORY_SEPARATOR.$raceId.'.json';

    if (!file_exists($raceJsonFile)) {
        continue;
    }
    $filteredHistory[$i] = $history;

    $seasonRaceAnalysisFiles = glob($seasonFolder.$season.DIRECTORY_SEPARATOR.'*.html');
    $seasonRaceAnalysisFiles = array_filter($seasonRaceAnalysisFiles, 'isRaceAnalysisFile');

    foreach ($seasonRaceAnalysisFiles as $seasonRaceAnalysisFile) {
        if (false !== strpos($seasonRaceAnalysisFile, $raceId.'_')) {
            $filteredHistory[$i]['file'] = $seasonRaceAnalysisFile;
            break;
        }
    }

    $raceData = json_decode(file_get_contents($raceJsonFile), true);

    $filteredHistory[$i]['driver'] = $raceData['driver'];
    $filteredHistory[$i]['ct_dry'] = $raceData['race']['ct_dry'];
    $filteredHistory[$i]['ct_wet'] = $raceData['race']['ct_wet'];

    $laps = $raceData['race']['laps'];
    array_shift($laps);

    if (isset($laps[0]['weather'])) {
        $wetLaps = array_filter($laps, fn ($lap) => 'W' === $lap['weather']);
        $nbWetLaps = count($wetLaps);
        $nbLaps = count($laps);
        $percentageWetLaps = round($nbWetLaps*100/$nbLaps);

        if ($nbWetLaps > 0) {
            $filteredHistory[$i]['wet'] = $percentageWetLaps;
        }
    } else {
        $filteredHistory[$i]['wet_na'] = true;
    }
}

$end = time();
$elapsed = $end - $start;

echo renderView('next-races-overview', compact('filteredHistory', 'medianHours', 'medianMinutes', 'elapsed'));
