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
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Download Post Race Data</title>
    <link rel="shortcut icon" href="img/favicon.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <script async src="https://kit.fontawesome.com/f711a4bfbd.js" crossorigin="anonymous"></script>
</head>

<body class="m-5">
<?php
include 'nav.php';
?>
<div class="mt-3"><?=$message?></div>
</body>
</html>

