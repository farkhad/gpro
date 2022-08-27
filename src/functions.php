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
    return preg_match("/(?<!\.replay)\.html$/", $element);
}
