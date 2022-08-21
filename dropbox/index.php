<?php

/**
 *
 * Authorize Dropbox
 */

 use Dropbox\Dropbox;

require './../vendor/autoload.php';

session_start();

$dropbox = new Dropbox;
if (!empty($_GET['code']) && !empty($_GET['state'])) {
    if ($dropbox->fromStorage('state') !== $_GET['state']) {
        $dropbox->clearStorage();
        echo "CSRF Attack!";
        exit;
    }
    $dropbox->unsetStorage('state');

    $creds = $dropbox->fetchTokenCreds($_GET['code'], $_GET['state']);
    if (!empty($creds['access_token']) && !empty($creds['refresh_token'])) {
        header("Location: " . $dropbox->fromStorage('redirectUri'));
        exit;
    }
} else {
    header("Location: " . $dropbox->getAuthUrl());
    exit;
}
