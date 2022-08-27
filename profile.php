<?php

/**
 *
 * Find Driver's Profile by ID against downloaded market database files
 */
require_once __DIR__ . '/src/functions.php';
$title = 'Driver\'s Profile';

// Perform search against limited number of market files
const MARKET_FILES_LIMIT = 200;

$marketFolder = 'market' . DIRECTORY_SEPARATOR;
$marketFiles = glob($marketFolder . '[!TD]*.php');
rsort($marketFiles);
$marketFiles = array_slice($marketFiles, 0, MARKET_FILES_LIMIT);

$profile = [];
$marketFile = '';
$driverId = '';

if (!empty($_GET['id'])) {
    $driverId = (int) $_GET['id'];

    foreach ($marketFiles as $marketFile) {
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

$content = renderView(
    'profile',
    compact(
        'marketFiles',
        'profile',
        'driverId',
        'marketFile'
    )
);

echo renderView('layout', compact('content', 'title'));
