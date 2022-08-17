<?php
/**
 * Home page
 * Display race analysis, market files
 */

$seasonFolder = 'seasons' . DIRECTORY_SEPARATOR;
$marketFolder = 'market' . DIRECTORY_SEPARATOR;

$seasons = glob($seasonFolder . '*', GLOB_ONLYDIR);
rsort($seasons);
$seasons = array_slice($seasons, 0, 2);

$raceAnalysisFiles = [];
array_walk($seasons, function (&$season) use ($seasonFolder, &$raceAnalysisFiles) {
    $seasonRaceAnalysisFiles = glob($season . DIRECTORY_SEPARATOR . '*.html');

    $season = str_replace($seasonFolder, '', $season);
    $raceAnalysisFiles[$season] = $seasonRaceAnalysisFiles;
});

$marketFiles = glob($marketFolder . '*.php');
rsort($marketFiles);
$marketFiles = array_slice($marketFiles, 0, 3);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Grand Prix Racing Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
</head>

<body class="m-5">
<?php
$page = pathinfo(__FILE__, PATHINFO_FILENAME);
include 'nav.php';
?>
<div class="row">
    <div class="col">
        Race Analysis from Latest 2 Seasons
        <ul>
            <?php foreach ($raceAnalysisFiles as $season => $seasonRaceAnalysisFiles) : ?>
            <li>Season <?=$season?>
                <ul>
                    <?php foreach ($seasonRaceAnalysisFiles as $seasonRaceAnalysisFile) : ?>
<?php
$dirSeparator = preg_quote(DIRECTORY_SEPARATOR);
$raceAnalysisFile = preg_replace('/[^' . $dirSeparator . ']+?' . $dirSeparator . '/is', '', $seasonRaceAnalysisFile)
?>
                        <li><a href="<?=$seasonRaceAnalysisFile?>" target="_blank"><?=$raceAnalysisFile?></a>
                    <?php endforeach; ?>
                </ul>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <div class="col">
        <?php if (!count($marketFiles)) : ?>
        Market files not found. <a href="market.php">Download</a> latest drivers market database file.
        <?php else : ?>
        Latest 3 Market Files
        <ul>
        <?php foreach ($marketFiles as $marketFile) :?>
            <li><?=$marketFile?></li>
        <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>
</body>

</html>