<?php

use Gpro\HomeParser;
use Gpro\SeasonCalendarParser;
use Gpro\TracksParser;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\SessionCookieJar;
use GuzzleHttp\RequestOptions;
use SleekDB\Store;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

$dbDir = __DIR__.DIRECTORY_SEPARATOR.DB_FOLDER_NAME;

session_start();

$credentials = ACCOUNTS[array_key_first(ACCOUNTS)];
extract($credentials);

$jar = new SessionCookieJar('gpro', true);

$client = new Client(['base_uri' => GPRO_URL, 'cookies' => $jar]);

$homeHtml = $client->post('Login.asp?Redirect=gpro.asp', [
    'form_params' => [
        'textLogin' => $username,
        'textPassword' => $password,
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

$calendarRecord = $calendarStore->updateOrInsert($fetchedSeasonCalendar);

$allTracksHtml = $client->get(
    'ViewTracks.asp',
    [
        RequestOptions::HEADERS => [
            'User-Agent' => GPRO_UA
        ],
    ]
)->getBody();

$fetchedTracks = (new TracksParser($allTracksHtml))->toArray();

$tracksStore = new Store("tracks", $dbDir, ['timeout' => false]);
$tracksStore->deleteStore();

$tracksStore = new Store("tracks", $dbDir, ['timeout' => false]);
$tracks = $tracksStore->insertMany($fetchedTracks);
$nbInsertedTracks = count($tracks);

if (php_sapi_name() === 'cli') {
    echo "Inserted ".count($calendarRecord['tracks'] ?? [])." season tracks".PHP_EOL;
    echo "Inserted ".$nbInsertedTracks." track records".PHP_EOL;
    exit;
}
