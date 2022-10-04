<?php
set_time_limit(2 * 60);

/**
 *
 * Fetch raw post race data for enclosed GPRO accounts
 */

use Gpro\CCPParser;
use Gpro\RaceAnalysisParser;
use Gpro\SponsorParser;
use Gpro\SponsorsParser;
use Gpro\StaffAndFacilitiesParser;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\SessionCookieJar;
use GuzzleHttp\RequestOptions;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';

require_once __DIR__ . '/src/functions.php';
$title = 'Download Post Race Data';
$message = '';

session_start();

foreach (\ACCOUNTS as $username => $password) {
    $userDir = str_replace('@', '_at_', $username);
    if (!is_dir('seasons')) {
        mkdir('seasons');
    }
    if (!is_dir('seasons' . DIRECTORY_SEPARATOR . $userDir)) {
        mkdir('seasons' . DIRECTORY_SEPARATOR . $userDir);
    }

    $jar = new SessionCookieJar('gpro', true);
    $client = new Client(['base_uri' => \GPRO_URL, 'cookies' => $jar]);
    $response = $client->post('Login.asp?Redirect=Help.asp', [
        'form_params' => [
            'textLogin' => $username,
            'textPassword' => $password,
            'token' => \HASH,
            'Logon' => 'Login',
            'LogonFake' => 'Login',
        ],
        'allow_redirects' => true,
        [
            RequestOptions::HEADERS => [
                'User-Agent' => \GPRO_UA
            ],
        ],
    ]);
    $response = $client->get('RaceAnalysis.asp');
    $postraceHtml = $response->getBody();

    $pattern = '%\<a href\=\"TrackDetails\.asp\?id\=([0-9]+)?">([^<]+?) \(.+?\<\/a\>.+?Season ([0-9]+?) - Race ([0-9]+?) \((?<myGroup>.+?)\)%is';
    if (preg_match($pattern, $postraceHtml, $matches)) {
        $trackName = str_replace(' ', '_', $matches[2]);
        $season = $matches[3];
        $race = $matches[4];
        $myGroup = $matches['myGroup'];

        $seasonFolder = 'seasons' . DIRECTORY_SEPARATOR . $userDir . DIRECTORY_SEPARATOR . $season;
        if (!is_dir($seasonFolder)) {
            mkdir($seasonFolder);
        }

        $raceAnalysisFileJSON = $seasonFolder . DIRECTORY_SEPARATOR
            . 'S' . $season . 'R' . $race . '.json';

        $raceAnalysis = new RaceAnalysisParser($postraceHtml);

        // Add Staff and Facilities information to Race Analysis JSON file
        $sfHtml = $client->get(
            'StaffAndFacilities.asp',
            [
                RequestOptions::HEADERS => [
                    'User-Agent' => \GPRO_UA
                ],
            ]
        )->getBody();
        $raceAnalysis->sf = (new StaffAndFacilitiesParser($sfHtml))->toArray();

        // Add Sponsors information to Race Analysis JSON file
        $sponsorsHtml = $client->get(
            'NegotiationsOverview.asp',
            [
                RequestOptions::HEADERS => [
                    'User-Agent' => \GPRO_UA
                ],
            ]
        )->getBody();
        $sponsorsParser = new SponsorsParser($sponsorsHtml);
        foreach ($sponsorsParser->negotiations as $i => $negotiation) {
            $sponsorUrl = 'NegotiateSponsor.asp?ID=' . $negotiation['id'];
            $sponsorHtml = $client->get(
                $sponsorUrl,
                [
                    RequestOptions::HEADERS => [
                        'User-Agent' => \GPRO_UA
                    ],
                ]
            )->getBody();
            $sponsorParser = new SponsorParser($sponsorHtml);
            $sponsorsParser->negotiations[$i]['attributes'] = $sponsorParser->attributes;
            $sponsorsParser->negotiations[$i]['feedback'] = $sponsorParser->feedback;
        }
        foreach ($sponsorsParser->contracts as $i => $contract) {
            if (!is_int($contract['id'])) {
                continue;
            }

            $sponsorUrl = 'NegotiateSponsor.asp?ID=' . $contract['id'];
            $sponsorHtml = $client->get(
                $sponsorUrl,
                [
                    RequestOptions::HEADERS => [
                        'User-Agent' => \GPRO_UA
                    ],
                ]
            )->getBody();
            $sponsorParser = new SponsorParser($sponsorHtml);
            $sponsorsParser->contracts[$i]['attributes'] = $sponsorParser->attributes;
        }
        $raceAnalysis->sponsors = $sponsorsParser->toArray();

        // Add CCP information to Race Analysis JSON file
        $testingHtml = $client->get(
            'Testing.asp',
            [
                RequestOptions::HEADERS => [
                    'User-Agent' => \GPRO_UA
                ],
            ]
        )->getBody();
        $raceAnalysis->carPoints = (new CCPParser($testingHtml))->toArray();

        file_put_contents($raceAnalysisFileJSON, $raceAnalysis->toJSON());

        // Store Race Analysis HTML page
        $raceAnalysisFile = $seasonFolder . DIRECTORY_SEPARATOR
            . 'S' . $season . 'R' . $race . '_' . $trackName . '.html';
        $postraceHtml = preg_replace('/src=["\']{1}.+?["\']{1}/is', '', $postraceHtml);
        file_put_contents($raceAnalysisFile, $postraceHtml);

        // Store Staff & Facilities HTML page
        $sfFile = $seasonFolder . DIRECTORY_SEPARATOR
            . 'S' . $season . 'R' . $race . '_SF_.html';
        file_put_contents($sfFile, preg_replace('/src=["\']{1}.+?["\']{1}/is', '', $sfHtml));

        // Store Sponsors HTML page
        $sponsorsFile = $seasonFolder . DIRECTORY_SEPARATOR
            . 'S' . $season . 'R' . $race . '_Sponsors_.html';
        file_put_contents($sponsorsFile, preg_replace('/src=["\']{1}.+?["\']{1}/is', '', $sponsorsHtml));

        // Store Testing HTML page
        $testingFile = $seasonFolder . DIRECTORY_SEPARATOR
            . 'S' . $season . 'R' . $race . '_Testing_.html';
        file_put_contents($testingFile, preg_replace('/src=["\']{1}.+?["\']{1}/is', '', $testingHtml));

        // Store Light Race Replay HTML page
        $raceReplayFile = $seasonFolder . DIRECTORY_SEPARATOR
            . 'S' . $season . 'R' . $race . '_' . $trackName . '.replay.html';
        $raceReplay = $client->get(
            'RaceReplay_light.asp?laps=all&Group=' . urlencode($myGroup),
            [
                RequestOptions::HEADERS => [
                    'User-Agent' => \GPRO_UA
                ],
            ]
        )->getBody();
        file_put_contents($raceReplayFile, $raceReplay);

        $message .= "\nPost Race data has been downloaded: \n"
            . "<ul>\n"
            . '<li><a href="' . $raceAnalysisFile . '" target="_blank">' . $raceAnalysisFile . "</a></li>\n"
            . '<li><a href="' . $raceAnalysisFileJSON . '" target="_blank">' . $raceAnalysisFileJSON . "</a></li>\n"
            . '<li><a href="' . $raceReplayFile . '" target="_blank">' . $raceReplayFile . "</a></li>\n"
            . '<li><a href="' . $sfFile . '" target="_blank">' . $sfFile . "</a></li>\n"
            . '<li><a href="' . $sponsorsFile . '" target="_blank">' . $sponsorsFile . "</a></li>\n"
            . '<li><a href="' . $testingFile . '" target="_blank">' . $testingFile . "</a></li>\n"
            . "</ul>\n";
    } else {
        $message .= "\nCannot find Season/Race html code.\n";
    }
}

if (php_sapi_name() === 'cli') {
    echo $message;
    exit;
}

$content = renderView('postrace', compact('message'));
echo renderView('layout', compact('content', 'title'));
