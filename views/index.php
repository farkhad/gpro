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
