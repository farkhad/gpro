<?php

/**
 * Fetch drivers market into PHP array market/Y-m-d.php
 */

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\SessionCookieJar;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';

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
]);

$gzfilename = 'market' . DIRECTORY_SEPARATOR . date('Y-m-d') . '.php';
$json = gzdecode($client->get('GetMarketFile.asp?market=drivers&type=json')->getBody());

file_put_contents($gzfilename, "<?php\n\nreturn " . var_export(json_decode($json, true), true) . ";");

echo "\nMarket file has been stored under <b>$gzfilename</b>\n";
