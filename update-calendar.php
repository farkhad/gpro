<?php
set_time_limit(2 * 60);

use Gpro\HomeParser;
use Gpro\SeasonCalendarParser;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\SessionCookieJar;
use GuzzleHttp\RequestOptions;
use SleekDB\Store;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';

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
$season = (new HomeParser($homeHtml))->season;

$seasonCalendarHtml = $client->get(
    'Calendar.asp',
    [
        RequestOptions::HEADERS => [
            'User-Agent' => GPRO_UA
        ],
    ]
)->getBody();

$fetchedSeasonCalendar = (new SeasonCalendarParser($seasonCalendarHtml))->toArray();

$insertCalendar = [
    'season' => $season,
    'tracks' => $fetchedSeasonCalendar['tracks'],
    'test' => $fetchedSeasonCalendar['test'],
];

$calendarStore = new Store("calendar", $dbDir, ['timeout' => false]);

$calendar = $calendarStore->findOneBy(['season', '=', $season]);
if (!empty($calendar['_id'])) {
    $insertCalendar['_id'] = $calendar['_id'];
}

$calendarRecord = $calendarStore->updateOrInsert($insertCalendar);

if (php_sapi_name() === 'cli') {
    echo "Inserted ".count($calendarRecord['tracks'] ?? [])." season tracks";
    exit;
}
