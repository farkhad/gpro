<?php
set_time_limit(2 * 60);

/**
 *
 * Fetch drivers market into PHP array market/Y-m-d.php
 */

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\SessionCookieJar;
use GuzzleHttp\RequestOptions;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/src/functions.php';

$cli = isCli();
$title = 'Download Market Database';

session_start();

$jar = new SessionCookieJar('gpro', true);
$client = new Client(['base_uri' => GPRO_URL, 'cookies' => $jar]);
if ($cli) {
    echo 'Logging in gpro.net...'.PHP_EOL;
}
$response = $client->post('Login.asp?Redirect=Help.asp', [
    'form_params' => [
        'textLogin' => USERNAME,
        'textPassword' => PASSWORD,
        'token' => HASH,
        'Logon' => 'Login',
        'LogonFake' => 'Login',
    ],
    'allow_redirects' => true,
    RequestOptions::HEADERS => [
        'User-Agent' => GPRO_UA
    ],
]);

if ($cli) {
    echo 'Downloading & extracting Drivers market file...'.PHP_EOL;
}
$json = gzdecode(
    $client->get(
        'GetMarketFile.asp?market=drivers&type=json',
        [
            RequestOptions::HEADERS => [
                'User-Agent' => GPRO_UA
            ],
        ]
    )->getBody()
);
if ($cli) {
    echo 'Downloading & extracting Tech Directors market file...'.PHP_EOL;
}
$jsonTechDirectors = gzdecode(
    $client->get(
        'GetMarketFile.asp?market=tds&type=json',
        [
            RequestOptions::HEADERS => [
                'User-Agent' => GPRO_UA
            ],
        ]
    )->getBody()
);

$marketFolder = 'market' . DIRECTORY_SEPARATOR;
$marketFile = $marketFolder . date('Y-m-d') . '.php';
$marketFileTechDirectors = $marketFolder . 'TD-' . date('Y-m-d') . '.php';

file_put_contents($marketFile, "<?php".PHP_EOL."return ".var_export(json_decode($json, true), true).";");
file_put_contents(
    $marketFileTechDirectors,
    "<?php".PHP_EOL."return ".var_export(json_decode($jsonTechDirectors, true), true).";"
);

$message = "<p>Drivers market file has been stored under <b>$marketFile</b></p>".PHP_EOL;
$message .= "<p>Tech Directors market file has been stored under <b>$marketFileTechDirectors</b></p>".PHP_EOL;

if ($cli) {
    $message = strip_tags($message);
    file_put_contents('market.log', date('d.m.Y H:i').PHP_EOL.$message);
    echo $message;
    exit;
}

$content = renderView('postrace', compact('message'));
echo renderView('layout', compact('content', 'title'));
