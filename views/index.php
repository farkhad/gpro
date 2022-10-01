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
                            $raceAnalysisFile = preg_replace('|[^' . $dirSeparator . ']+?' . $dirSeparator . '|is', '', $seasonRaceAnalysisFile);

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
                                <a href="<?= $seasonRaceAnalysisFile ?>" target="_blank"><?= $raceAnalysisFile ?></a>
                                <?php if (!empty($jsonFile)) : ?>
                                    <sup><a href="javascript:void(0)" data-bs-json="<?= $jsonFile ?>" data-bs-toggle="modal" data-bs-target="#lapsModal">Laps Graph</a></sup>
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
                <?php foreach ($marketFiles as $marketFile) : ?>
                    <li><?= $marketFile ?></li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>

        <?php if (!count($marketFilesTechDirectors)) : ?>
            Market files not found. <a href="market.php">Download</a> latest tech directors market database file.
        <?php else : ?>
            Latest <?= \MARKET_FILES_LIMIT ?> Tech Directors Market Files
            <ol>
                <?php foreach ($marketFilesTechDirectors as $marketFile) : ?>
                    <li><?= $marketFile ?></li>
                <?php endforeach; ?>
            </ol>
        <?php endif; ?>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="lapsModal" tabindex="-1" aria-labelledby="lapsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lapsModalLabel">Race Laps Analysis</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div style="position:relative;margin:auto;height:80vh;width:80vw;">
                    <canvas id="chart"></canvas>
                </div>
            </div>
            <!-- <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div> -->
        </div>
    </div>
</div>
<script src="js/jquery.min.js"></script>
<script src="js/chart.min.js"></script>
<script>
    const lapsModal = document.querySelector('#lapsModal');
    lapsModal.addEventListener('show.bs.modal', evt => {
        // Button that triggered the modal
        const button = evt.relatedTarget;
        // Extract info from data-bs-* attributes
        const jsonFile = button.getAttribute('data-bs-json');

        $.ajax(jsonFile).done((data) => {
            const laps = data.race.laps;
            let xValues = [],
                yValues = [];

            laps.forEach((el, index) => {
                let timeSlots = el.time.split(':'); // split it at the colons
                let seconds = 0.0;
                if (timeSlots.length === 2) {
                    seconds = parseInt(timeSlots[0]) * 60 + parseFloat(timeSlots[1]);
                } else {
                    seconds = parseFloat(timeSlots[0]);
                }

                if (timeSlots[0] !== '-') {
                    xValues.push(index);
                    yValues.push(seconds);
                }
            });

            new Chart("chart", {
                type: "line",
                data: {
                    labels: xValues,
                    datasets: [{
                        label: 'Lap Time (seconds)',
                        backgroundColor: "rgba(25,135,84, 1.0)",
                        borderColor: "rgba(25,135,84, 0.1)",
                        data: yValues
                    }]
                },
                options: {
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: 'Laps'
                            },
                            beginAtZero: false,
                            ticks: {
                                color: 'rgb(0,0,0)'
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Lap Time'
                            },
                            beginAtZero: false,
                            ticks: {
                                // For a category axis, the val is the index so the lookup via getLabelForValue is needed
                                callback: function(val, index) {
                                    let seconds = val % 60;
                                    let min = parseInt((val - seconds) / 60);
                                    let secondsWithLeadingZero = (seconds < 10 ? '0' : '') + seconds;
                                    return min > 0 ? min + ':' + secondsWithLeadingZero : secondsWithLeadingZero;
                                },
                                color: 'rgb(0,0,0)'
                            }
                        }
                    }
                }
            });
        });
    });
</script>
