<?php

use Gpro\TracksParser;
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

$response = $client->post('Login.asp?Redirect=Help.asp', [
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
    echo "Inserted ".$nbInsertedTracks." records";
    exit;
}
