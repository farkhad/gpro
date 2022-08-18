<?php

/**
 * Fetch drivers market into PHP array market/Y-m-d.php
 */

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\SessionCookieJar;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/config.php';

session_start();

$marketFile = 'market' . DIRECTORY_SEPARATOR . date('Y-m-d') . '.php';
if (!file_exists($marketFile)) {
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

    $json = gzdecode($client->get('GetMarketFile.asp?market=drivers&type=json')->getBody());

    file_put_contents($marketFile, "<?php\n\nreturn " . var_export(json_decode($json, true), true) . ";");

    $message = "\nMarket file has been stored under <b>$marketFile</b>\n";
} else {
    $message = "\nMarket file <b>$marketFile</b> exists already.\n";
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
    <title>Download Market Database</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
</head>

<body class="m-5">
    <?php
    $page = pathinfo(__FILE__, PATHINFO_FILENAME);
    include 'nav.php';
    ?>
    <div class="mt-3"><?= $message ?></div>
</body>

</html>
