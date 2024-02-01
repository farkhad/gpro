<?php
set_time_limit(2 * 60);

use Gpro\CCPParser;
use Gpro\HomeParser;
use Gpro\RaceAnalysisParser;
use Gpro\SeasonCalendarParser;
use Gpro\SponsorParser;
use Gpro\SponsorsParser;
use Gpro\StaffAndFacilitiesParser;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\SessionCookieJar;
use GuzzleHttp\RequestOptions;
use SleekDB\Store;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/src/functions.php';

$cli = isCli();

if (!class_exists('SleekDB\Store')) {
    $message = "Navigate to <i>GPRO Home Server</i> folder and run console commands:".PHP_EOL
        ."<ol><li><pre>composer install</pre></li>".PHP_EOL
        ."<li><pre>php sync.php</pre></li></ol>".PHP_EOL
    ;
    echo $cli ? strip_tags($message) : $message;
    exit;
}

$title = 'Download Post Race Data';
$message = '';
$dbDir = __DIR__.DIRECTORY_SEPARATOR.DB_FOLDER_NAME;

session_start();

if (!is_dir('seasons')) {
    if ($cli) {
        echo 'Creating "seasons" directory...'.PHP_EOL;
    }
    mkdir('seasons');
}
if (!is_dir('seasons' . DIRECTORY_SEPARATOR . FOLDER_NAME)) {
    if ($cli) {
        echo 'Creating "seasons'.DIRECTORY_SEPARATOR.FOLDER_NAME.'" directory...'.PHP_EOL;
    }
    mkdir('seasons' . DIRECTORY_SEPARATOR . FOLDER_NAME);
}

$jar = new SessionCookieJar('gpro', true);
$client = new Client(['base_uri' => GPRO_URL, 'cookies' => $jar]);
if ($cli) {
    echo 'Logging in gpro.net...'.PHP_EOL;
}
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

$homeParser = new HomeParser($homeHtml);
$season = $homeParser->season;
$group = $homeParser->group;
$nextTrackId = $homeParser->nextTrackId;

if ($cli) {
    echo 'Fetching Season Calendar of Season '.$season.' & Group '.$group.'...'.PHP_EOL;
}
$seasonCalendarHtml = $client->get(
    'Calendar.asp',
    [
        RequestOptions::HEADERS => [
            'User-Agent' => GPRO_UA
        ],
        RequestOptions::QUERY => [
            'Group' => $group,
        ],
    ]
)->getBody();

$fetchedSeasonCalendar = (new SeasonCalendarParser($seasonCalendarHtml))->toArray();
$fetchedSeasonCalendar['season'] = $season;

$calendarStore = new Store("calendar", $dbDir, ['timeout' => false]);

$calendar = $calendarStore->findOneBy(['season', '=', $season]);
if (!empty($calendar['_id'])) {
    $fetchedSeasonCalendar['_id'] = $calendar['_id'];
}

if ($cli) {
    echo 'Storing Season Calendar in local database...'.PHP_EOL;
}
$calendarRecord = $calendarStore->updateOrInsert($fetchedSeasonCalendar);

if ($cli) {
    echo 'Fetching Race Analysis page...'.PHP_EOL;
}
$postraceHtml = $client->get(
    'RaceAnalysis.asp',
    [
        RequestOptions::HEADERS => [
            'User-Agent' => GPRO_UA
        ],
    ]
)->getBody();

$pattern = '%\<a href\=\"TrackDetails\.asp\?id\=([0-9]+)?">([^<]+?) \(.+?\<\/a\>.+?Season ([0-9]+?) - Race ([0-9]+?) \((?<myGroup>.+?)\)%is';
if (preg_match($pattern, $postraceHtml, $matches)) {
    $trackName = str_replace(' ', '_', $matches[2]);
    $season = $matches[3];
    $race = $matches[4];
    $myGroup = $matches['myGroup'];

    $seasonFolder = 'seasons' . DIRECTORY_SEPARATOR . FOLDER_NAME . DIRECTORY_SEPARATOR . $season;
    if (!is_dir($seasonFolder)) {
        mkdir($seasonFolder);
    }

    $raceAnalysisFileJSON = $seasonFolder . DIRECTORY_SEPARATOR
        . 'S' . $season . 'R' . $race . '.json';

    $raceAnalysis = new RaceAnalysisParser($postraceHtml);

    // Add Staff and Facilities information to Race Analysis JSON file
    if ($cli) {
        echo 'Fetching Staff & Facilities page...'.PHP_EOL;
    }
    $sfHtml = $client->get(
        'StaffAndFacilities.asp',
        [
            RequestOptions::HEADERS => [
                'User-Agent' => GPRO_UA
            ],
        ]
    )->getBody();
    $raceAnalysis->sf = (new StaffAndFacilitiesParser($sfHtml))->toArray();

    // Add Sponsors information to Race Analysis JSON file
    if ($cli) {
        echo 'Fetching Sponsors Negotiations Overview page...'.PHP_EOL;
    }
    $sponsorsHtml = $client->get(
        'NegotiationsOverview.asp',
        [
            RequestOptions::HEADERS => [
                'User-Agent' => GPRO_UA
            ],
        ]
    )->getBody();
    $sponsorsParser = new SponsorsParser($sponsorsHtml);
    foreach ($sponsorsParser->negotiations as $i => $negotiation) {
        $sponsorUrl = 'NegotiateSponsor.asp?ID=' . $negotiation['id'];
        if ($cli) {
            echo 'Fetching Negotiate Sponsor ['.$negotiation['id'].'] page...'.PHP_EOL;
        }
        $sponsorHtml = $client->get(
            $sponsorUrl,
            [
                RequestOptions::HEADERS => [
                    'User-Agent' => GPRO_UA
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
        if ($cli) {
            echo 'Fetching Contract Sponsor ['.$contract['id'].'] page...'.PHP_EOL;
        }
        $sponsorHtml = $client->get(
            $sponsorUrl,
            [
                RequestOptions::HEADERS => [
                    'User-Agent' => GPRO_UA
                ],
            ]
        )->getBody();
        $sponsorParser = new SponsorParser($sponsorHtml);
        $sponsorsParser->contracts[$i]['attributes'] = $sponsorParser->attributes;
    }
    $raceAnalysis->sponsors = $sponsorsParser->toArray();

    // Add CCP information to Race Analysis JSON file
    if ($cli) {
        echo 'Fetching Testing page...'.PHP_EOL;
    }
    $testingHtml = $client->get(
        'Testing.asp',
        [
            RequestOptions::HEADERS => [
                'User-Agent' => GPRO_UA
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
    if ($cli) {
        echo 'Fetching Light Race Replay page...'.PHP_EOL;
    }
    $raceReplayFile = $seasonFolder . DIRECTORY_SEPARATOR
        . 'S' . $season . 'R' . $race . '_' . $trackName . '.replay.html';
    $raceReplay = $client->get(
        'RaceReplay_light.asp?laps=all&Group=' . urlencode($myGroup),
        [
            RequestOptions::HEADERS => [
                'User-Agent' => GPRO_UA
            ],
        ]
    )->getBody();
    file_put_contents($raceReplayFile, $raceReplay);

    $message .= 'Post Race data has been downloaded:'.PHP_EOL
        .'<ul>'.PHP_EOL
        .'<li><a href="'.$raceAnalysisFile.'" target="_blank">'.$raceAnalysisFile.'</a></li>'.PHP_EOL
        .'<li><a href="'.$raceAnalysisFileJSON.'" target="_blank">'.$raceAnalysisFileJSON.'</a></li>'.PHP_EOL
        .'<li><a href="'.$raceReplayFile.'" target="_blank">'.$raceReplayFile.'</a></li>'.PHP_EOL
        .'<li><a href="'.$sfFile.'" target="_blank">'.$sfFile.'</a></li>'.PHP_EOL
        .'<li><a href="'.$sponsorsFile.'" target="_blank">'.$sponsorsFile.'</a></li>'.PHP_EOL
        .'<li><a href="'.$testingFile.'" target="_blank">' . $testingFile.'</a></li>'.PHP_EOL
        .'</ul>'.PHP_EOL
    ;
    $message .= '<p>Season Calendar has been updated.</p>'.PHP_EOL
    ;
} else {
    $message .= 'Cannot find Season/Race html code.'.PHP_EOL;
}

if ($cli) {
    $message = strip_tags($message);
    file_put_contents('postrace.log', date('d.m.Y H:i').PHP_EOL.$message);
    echo $message;
    exit;
}

$content = renderView('postrace', compact('message'));
echo renderView('layout', compact('content', 'title'));
