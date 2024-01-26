<?php

/**
 *
 * Find Driver's Profile by ID against downloaded market database files
 */

use Gpro\DriverProfileParser;
use Gpro\SeasonCalendarParser;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\SessionCookieJar;
use GuzzleHttp\RequestOptions;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/src/functions.php';

$title = 'Driver\'s Profile';
$marketFolder = 'market'.DIRECTORY_SEPARATOR;
$marketFiles = glob($marketFolder . '[!TD]*.php');

session_start();

$profile = [];
$marketFile = '';
$driverId = '';
$historySeason = '';
$historyRace = '';

$start = time();

if (!empty($_GET['id'])) {
    $driverId = (int) $_GET['id'];

    $credentials = ACCOUNTS[array_key_first(ACCOUNTS)];
    extract($credentials);

    $client = new Client(['base_uri' => GPRO_URL, 'cookies' => new SessionCookieJar('gpro', true)]);

    $client->post('Login.asp?Redirect=Help.asp', [
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
    ]);

    $driverHtml = $client->get(
        'DriverProfile.asp',
        [
            RequestOptions::HEADERS => [
                'User-Agent' => GPRO_UA
            ],
            RequestOptions::QUERY => [
                'ID' => $driverId,
            ],
        ]
    )->getBody();

    $driverProfileParser = new DriverProfileParser($driverHtml);
    $historySeason = $driverProfileParser->startedWorking['season'];
    $historyRace = $driverProfileParser->startedWorking['race'];

    $historySeasonCalendarHtml = $client->get(
        'History.asp',
        [
            RequestOptions::HEADERS => [
                'User-Agent' => GPRO_UA
            ],
            RequestOptions::QUERY => [
                'table' => 'Calendar',
                'season' => $historySeason,
            ],
        ]
    )->getBody();

    $seasonCalendarParser = new SeasonCalendarParser($historySeasonCalendarHtml);
    $historyDate = $seasonCalendarParser->calendar[$historyRace-1]['date'];

    $dt = new DateTime('now', new DateTimeZone(GPRO_TIMEZONE));

    $checkDateStart = $dt->setTimestamp($historyDate)->modify('-1 week')->format('Y-m-d');
    $checkDateEnd = $dt->modify('+2 week')->format('Y-m-d');

    $checkDates = [];
    $checkDate = $checkDateEnd;
    while ($checkDateStart !== $checkDate) {
        $checkDates[] = $checkDate;
        $checkDate = $dt->modify('-1 day')->format('Y-m-d');
    }
    $checkDates[] = $checkDateStart;

    foreach ($checkDates as $checkDate) {
        $marketFile = $marketFolder.$checkDate.'.php';
        if (!file_exists($marketFile)) {
            continue;
        }

        $content = file_get_contents($marketFile);
        if (false !== strpos($content, "'ID' => $driverId,")) {
            $drivers = require $marketFile;
            foreach ($drivers['drivers'] as $driver) {
                if ($driver['ID'] === $driverId) {
                    $profile = $driver;
                    break;
                }
            }
            break;
        }
    }
}

$timeSpent = time()-$start;

$content = renderView(
    'profile',
    compact(
        'marketFiles',
        'profile',
        'driverId',
        'marketFile',
        'historySeason',
        'historyRace',
        'timeSpent'
    )
);

echo renderView('layout', compact('content', 'title'));
