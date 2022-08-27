<?php

/**
 *
 * Fetch raw post race analysis seasons/SS/SXXRYY TrackName.html
 */

use Gpro\RaceAnalysisParser;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\SessionCookieJar;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';

require_once __DIR__ . '/src/functions.php';
$title = 'Download Post Race Data';

session_start();

$jar = new SessionCookieJar('gpro', true);
$client = new Client(['base_uri' => \GPRO_URL, 'cookies' => $jar]);
$response = $client->post('Login.asp?Redirect=RaceAnalysis.asp', [
    'form_params' => [
        'textLogin' => \USERNAME,
        'textPassword' => \PASSWORD,
        'token' => \HASH,
        'Logon' => 'Login',
        'LogonFake' => 'Login',
    ],
    'allow_redirects' => true,
]);
$postraceHtml = $response->getBody();

$pattern = '%\<a href\=\"TrackDetails\.asp\?id\=([0-9]+)?">([^<]+?) \(.+?\<\/a\>.+?Season ([0-9]+?) - Race ([0-9]+?) \((?<myGroup>.+?)\)%is';
if (preg_match($pattern, $postraceHtml, $matches)) {
    $trackName = str_replace(' ', '_', $matches[2]);
    $season = $matches[3];
    $race = $matches[4];
    $myGroup = $matches['myGroup'];

    $seasonFolder = 'seasons' . DIRECTORY_SEPARATOR . $season;
    if (!is_dir($seasonFolder)) {
        mkdir($seasonFolder);
    }

    $raceAnalysisFileJSON = $seasonFolder . DIRECTORY_SEPARATOR
    . 'S' . $season . 'R' . $race . '.json';
    file_put_contents($raceAnalysisFileJSON, (new RaceAnalysisParser($postraceHtml))->toJSON());

    $raceAnalysisFile = $seasonFolder . DIRECTORY_SEPARATOR
        . 'S' . $season . 'R' . $race . '_' . $trackName . '.html';
    $postraceHtml = preg_replace('/src=["\']{1}.+?["\']{1}/is', '', $postraceHtml);
    file_put_contents($raceAnalysisFile, $postraceHtml);

    $raceReplayFile = $seasonFolder . DIRECTORY_SEPARATOR
    . 'S' . $season . 'R' . $race . '_' . $trackName . '.replay.html';
    $raceReplay = $client->get('RaceReplay_light.asp?laps=all&Group=' . urlencode($myGroup))->getBody();
    file_put_contents($raceReplayFile, $raceReplay);

    $message = "\nPost Race data has been downloaded: \n"
        . "<ul>\n"
        . '<li><a href="' . $raceAnalysisFile . '" target="_blank">' . $raceAnalysisFile . "</a></li>\n"
        . '<li><a href="' . $raceAnalysisFileJSON . '" target="_blank">' . $raceAnalysisFileJSON . "</a></li>\n"
        . '<li><a href="' . $raceReplayFile . '" target="_blank">' . $raceReplayFile . "</a></li>\n"
        . "</ul>\n";
} else {
    $message = "\nCannot find Season/Race html code.\n";
}

if (php_sapi_name() === 'cli') {
    echo $message;
    exit;
}

$content = renderView('postrace', compact('message'));
echo renderView('layout', compact('content', 'title'));
