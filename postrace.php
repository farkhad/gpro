<?php

/**
 * Fetch raw post race analysis seasons/SS/SXXRYY TrackName.html
 */

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\SessionCookieJar;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';

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

$pattern = '%\<a href\=\"TrackDetails\.asp\?id\=([0-9]+)?">([^<]+?) \(.+?\<\/a\>.+?Season ([0-9]+?) - Race ([0-9]+?) \(%is';
if (false !== preg_match($pattern, $postraceHtml, $matches)) {
    $trackName = $matches[2];
    $season = $matches[3];
    $race = $matches[4];

    $seasonFolder = 'seasons' . DIRECTORY_SEPARATOR . $season;
    if (!is_dir($seasonFolder)) {
        mkdir($seasonFolder);
    }

    $raceAnalysisFile = $seasonFolder . DIRECTORY_SEPARATOR
        . 'S' . $season . 'R' . $race . ' ' . $trackName . '.html';

    // wipe out <script src="..." ...>
    $postraceHtml = preg_replace('/src=["\']{1}.+?["\']{1}/is', '', $postraceHtml);
    file_put_contents($raceAnalysisFile, $postraceHtml);

    echo 'Post race analysis has been stored under '
        . '<b><a href="' . $raceAnalysisFile . '" target="_blank">' . $raceAnalysisFile . '</a></b>';
} else {
    echo 'Cannot find Season/Race html code.';
}
