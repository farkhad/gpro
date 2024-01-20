<?php

function renderView(string $view, array $data = []) : string|bool
{
    $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR
        . 'views' . DIRECTORY_SEPARATOR . $view . '.php';

    if (!is_readable($path)) {
        return false;
    }

    ob_start();

    extract($data);
    require $path;

    return ob_get_clean();
}

function isRaceAnalysisFile($element) {
    $pattern = '|S[0-9]+?R[0-9]+?[_ ]{1}[^\.]+?[^_]{1}?\.html$|';
    return preg_match($pattern, $element);
}

function sortRaceFiles($a, $b) {
    $pattern = '/S[0-9]+?R([0-9]+)/';
    preg_match($pattern, $a, $mA);
    preg_match($pattern, $b, $mB);

    return $mB[1] <=> $mA[1];
}
