<?php

use Dropbox\Dropbox;

require 'vendor/autoload.php';

session_start();

if (!empty($_GET['season']) && !empty($_GET['file'])) {
    $season = (int) $_GET['season'];
    $fileName = $_GET['file'];

    $seasonFolder = 'seasons' . DIRECTORY_SEPARATOR . $season;
    $file = $seasonFolder . DIRECTORY_SEPARATOR . $fileName;
    if (file_exists($file)) {
        $contents = file_get_contents($file);
    } else {
        echo $file . " does not exist.";
        exit;
    }

    $dropbox = new Dropbox;

    $dropbox->toStorage([
        'redirectUri' => 'upload.php?' . http_build_query([
            'season' => $season,
            'file' => $fileName
        ]),
    ]);

    $meta = $dropbox->simpleUpload('/seasons/' . $season . '/' . $fileName, $contents, 'overwrite');

    Dropbox::updateListOfUploadedFiles($season, $meta);
}

header('Location: ./');
exit;
