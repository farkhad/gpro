<?php
set_time_limit(2 * 60);

/**
 *
 * Fetch drivers market into PHP array market/Y-m-d.php
 */

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\SessionCookieJar;
use GuzzleHttp\RequestOptions;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';

require_once __DIR__ . '/src/functions.php';
$title = 'Download Market Database';

session_start();

$jar = new SessionCookieJar('gpro', true);
$client = new Client(['base_uri' => \GPRO_URL, 'cookies' => $jar]);
$response = $client->post('Login.asp?Redirect=gpro.asp', [
    'form_params' => [
        'textLogin' => \USERNAME,
        'textPassword' => \PASSWORD,
        'token' => \HASH,
        'Logon' => 'Login',
        'LogonFake' => 'Login',
    ],
    'allow_redirects' => true,
    RequestOptions::HEADERS => [
        'User-Agent' => \GPRO_UA
    ],
]);

$json = gzdecode(
    $client->get(
        'GetMarketFile.asp?market=drivers&type=json',
        [
            RequestOptions::HEADERS => [
                'User-Agent' => \GPRO_UA
            ],
        ]
    )->getBody()
);
$jsonTechDirectors = gzdecode(
    $client->get(
        'GetMarketFile.asp?market=tds&type=json',
        [
            RequestOptions::HEADERS => [
                'User-Agent' => \GPRO_UA
            ],
        ]
    )->getBody()
);

$marketFolder = 'market' . DIRECTORY_SEPARATOR;
$marketFile = $marketFolder . date('Y-m-d') . '.php';
$marketFileTechDirectors = $marketFolder . 'TD-' . date('Y-m-d') . '.php';

file_put_contents($marketFile, "<?php\n\nreturn " . var_export(json_decode($json, true), true) . ";");
file_put_contents(
    $marketFileTechDirectors,
    "<?php\n\nreturn " . var_export(json_decode($jsonTechDirectors, true), true) . ";"
);

$message = "\n<p>Market file has been stored under <b>$marketFile</b></p>\n";
$message .= "<p>Tech Directors Market file has been stored under <b>$marketFileTechDirectors</b></p>\n";

if (php_sapi_name() === 'cli') {
    echo $message;
    exit;
}

$content = renderView('postrace', compact('message'));
echo renderView('layout', compact('content', 'title'));
