<?php
/**
 * Home page
 * Display race analysis, market files
 */
const MARKET_FILES_LIMIT = 5;

$seasonFolder = 'seasons' . DIRECTORY_SEPARATOR;
$marketFolder = 'market' . DIRECTORY_SEPARATOR;

$seasons = glob($seasonFolder . '*', GLOB_ONLYDIR);
rsort($seasons);
$seasons = array_slice($seasons, 0, 2);

$raceAnalysisFiles = [];
array_walk($seasons, function (&$season) use ($seasonFolder, &$raceAnalysisFiles) {
    $seasonRaceAnalysisFiles = glob($season . DIRECTORY_SEPARATOR . '*[!replay].html');
    usort($seasonRaceAnalysisFiles, function ($a, $b) {
        $pattern = '/S[0-9]+?R([0-9]+)/';
        preg_match($pattern, $a, $mA);
        preg_match($pattern, $b, $mB);

        return $mB[1] <=> $mA[1];
    });

    $season = str_replace($seasonFolder, '', $season);
    $raceAnalysisFiles[$season] = $seasonRaceAnalysisFiles;
});

$marketFiles = glob($marketFolder . '[!TD]*.php');
rsort($marketFiles);
$marketFiles = array_slice($marketFiles, 0, \MARKET_FILES_LIMIT);

$marketFilesTechDirectors = glob($marketFolder . '[TD-]*.php');
rsort($marketFilesTechDirectors);
$marketFilesTechDirectors = array_slice($marketFilesTechDirectors, 0, \MARKET_FILES_LIMIT);
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GPRO Home Server</title>
    <link rel="shortcut icon" href="img/favicon.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <script async src="https://kit.fontawesome.com/f711a4bfbd.js" crossorigin="anonymous"></script>
</head>

<body class="m-5">
<?php
include 'nav.php';
?>
<div class="row mb-3">
    <div class="col">
        <a href="postrace.php">Download</a> latest Post Race data.
    </div>
    <div class="col">
        <a href="market.php">Download</a> latest Market Database.
    </div>
</div>
<div class="row">
    <div class="col">
        Post Race Data from Latest 2 Seasons
        <ul>
            <?php foreach ($raceAnalysisFiles as $season => $seasonRaceAnalysisFiles) : ?>
            <li>Season <?= $season ?>
                <ul>
                    <?php foreach ($seasonRaceAnalysisFiles as $seasonRaceAnalysisFile) : ?>
<?php
$dirSeparator = preg_quote(DIRECTORY_SEPARATOR);
$raceAnalysisFile = preg_replace('/[^' . $dirSeparator . ']+?' . $dirSeparator . '/is', '', $seasonRaceAnalysisFile);

$raceReplayFile = str_replace('.html', '.replay.html', $raceAnalysisFile);
$raceReplayFile = $seasonFolder . $season . DIRECTORY_SEPARATOR . $raceReplayFile;

if (!file_exists($raceReplayFile)) {
    unset($raceReplayFile);
}

if (preg_match('/(S[0-9]+?R[0-9]+?)[_ ]{1}/i', $raceAnalysisFile, $matches)) {
    $jsonFile = $seasonFolder . $season . DIRECTORY_SEPARATOR . $matches[1] . '.json';
    if (!file_exists($jsonFile)) {
        unset($jsonFile);
    }
}
?>
                        <li>
                            <a href="<?= $seasonRaceAnalysisFile ?>" target="_blank"><?=$raceAnalysisFile?></a>
                            <?php if (!empty($jsonFile)) : ?>
                                <sup><a href="<?= $jsonFile ?>" target="_blank">JSON</a></sup>
                            <?php endif; ?>
                            <?php if (!empty($raceReplayFile)) : ?>
                                <sup><a href="<?= $raceReplayFile ?>" target="_blank">Replay</a></sup>
                            <?php endif; ?>
                        </li>
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
        Latest <?= \MARKET_FILES_LIMIT ?> Market Files
        <ol>
        <?php foreach ($marketFiles as $marketFile) :?>
            <li><?=$marketFile?></li>
        <?php endforeach; ?>
        </ol>
        <?php endif; ?>

        <?php if (!count($marketFilesTechDirectors)) : ?>
        Market files not found. <a href="market.php">Download</a> latest tech directors market database file.
        <?php else : ?>
        Latest <?= \MARKET_FILES_LIMIT ?> Tech Directors Market Files
        <ol>
        <?php foreach ($marketFilesTechDirectors as $marketFile) :?>
            <li><?= $marketFile ?></li>
        <?php endforeach; ?>
        </ol>
        <?php endif; ?>
    </div>
</div>
</body>

</html>
