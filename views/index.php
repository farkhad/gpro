<div class="row">
    <div class="col">
        <a href="postrace.php">Download</a> latest Post Race data.
    </div>
    <div class="col">
        <a href="market.php">Download</a> latest Market Database.
        <p class="d-inline-flex gap-1">
            <button
                class="btn btn-sm btn-secondary"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#market-files"
                aria-expanded="false"
                aria-controls="market-files"
            >
                Latest 5 Market Files
            </button>
            <div class="collapse" id="market-files">
            <div class="card card-body">
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
        </p>
    </div>
</div>
<div class="row">
    <div class="col">
    <?php foreach ($raceAnalysisFiles as $userDir => $seasons) : ?>
        <h3><?= str_replace('seasons' . DIRECTORY_SEPARATOR, '', $userDir) ?></h3>
        <?php $curSeason = array_key_first($seasons); ?>
        <?php if (isset($sponsors[$userDir])) :?>
        <table class="table table-striped">
            <tr>
                <th colspan="18"><i>Season <?= $curSeason?></i></th>
            </tr>
            <tr>
                <th scope="col">Sponsors</th>
                <?php
                for ($n = 1; $n < 18; $n++) :
                    $pos = null;
                    if (isset($seasonRaces['S'.$curSeason.'R'.$n])) {
                        $pos = $seasonRaces['S'.$curSeason.'R'.$n]['race']['finish'];
                    }
                ?>
                <th scope="col">R<?= $n.(null !== $pos ? '<sup>'.$pos.'</sup>' : '')?></th>
                <?php endfor; ?>
            </tr>
            <?php
            $totals = [];
            $i = 0;
            foreach ($sponsors[$userDir] as $sponsorName => $sponsorData) :
            ?>
            <tr>
                <td><?= ++$i.'. '.$sponsorName?></td>
                <?php
                for ($n = 1; $n < 18; $n++) :
                    $progress = '';

                    $curProgress = $sponsorData['S'.$curSeason.'R'.$n]['progress'] ?? null;
                    if (isset($curProgress)) {
                        $progress = $curProgress;

                        $prevProgress = $n > 1
                            ? ($sponsorData['S'.$curSeason.'R'.($n-1)]['progress'] ?? null)
                            : null
                        ;

                        $gained = 0;
                        if (isset($prevProgress)) {
                            $gained = $curProgress - $prevProgress;
                        } elseif ($curProgress < 100) {
                            $gained = $curProgress;
                        }

                        $progress .= '<span class="text-success">+'.$gained.'</span>';

                        if (!isset($totals[$n])) {
                            $totals[$n] = $gained;
                        } else {
                            $totals[$n] += $gained;
                        }
                    }
                ?>
                <td><?= $progress ?></td>
                <?php endfor; ?>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td>Total</td>
                <?php for ($n = 1; $n < 18; $n++) : ?>
                    <?php if (isset($totals[$n])) : ?>
                    <td class="text-success">+<?= $totals[$n] ?></td>
                    <?php else : ?>
                    <td></td>
                    <?php endif; ?>
                <?php endfor; ?>
            </tr>
        </table>
        <?php endif; ?>
        <ul>
            <?php foreach ($seasons as $season => $seasonRaceAnalysisFiles) : ?>
            <li>Season <?= $season ?>
                <ul>
                    <?php foreach ($seasonRaceAnalysisFiles as $seasonRaceAnalysisFile) : ?>
                        <?php
                        $seasonFolder = $userDir . DIRECTORY_SEPARATOR;
                        $dirSeparator = preg_quote(DIRECTORY_SEPARATOR);
                        $raceAnalysisFile = preg_replace('|[^' . $dirSeparator . ']+?' . $dirSeparator . '|is', '', $seasonRaceAnalysisFile);

                        $raceReplayFile = str_replace('.html', '.replay.html', $raceAnalysisFile);
                        $raceReplayFile = $seasonFolder . $season . DIRECTORY_SEPARATOR . $raceReplayFile;

                        if (!file_exists($raceReplayFile)) {
                            unset($raceReplayFile);
                        }

                        if (preg_match('/(S[0-9]+?R[0-9]+?)[_ ]{1}/i', $raceAnalysisFile, $matches)) {
                            $jsonFileName = $matches[1] . '.json';
                            $jsonFile = $seasonFolder . $season . DIRECTORY_SEPARATOR . $jsonFileName;
                            if (!file_exists($jsonFile)) {
                                unset($jsonFile);
                            }
                        }
                        ?>
                        <li>
                            <a href="<?= $seasonRaceAnalysisFile ?>" target="_blank"><?= $raceAnalysisFile ?></a>
                            <?php if (!empty($jsonFile)) : ?>
                                <sup><a href="javascript:void(0)" data-bs-track="<?=
                                    preg_replace(['/^.+?_/', '/\.html/'], ['', ''], $raceAnalysisFile) ?>" data-bs-season="<?= $season ?>" data-bs-json="<?= $jsonFileName ?>" data-bs-toggle="modal" data-bs-target="#lapsModal">L-Chart</a></sup>
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
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="lapsModal" tabindex="-1" aria-labelledby="lapsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="lapsModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div style="position:relative;margin:auto;height:80vh;width:80vw;">
                    <canvas id="chart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="js/jquery.min.js"></script>
<script src="js/chart.min.js"></script>
<script>
    const lapsModal = document.querySelector('#lapsModal');
    const charts = [];

    lapsModal.addEventListener('show.bs.modal', evt => {
        // Button that triggered the modal
        const button = evt.relatedTarget;

        $('#lapsModalLabel').text(
            button.getAttribute('data-bs-track')
            + ' Race Laps Analysis'
        );

        // Extract info from data-bs-* attributes
        const jsonFile = button.getAttribute('data-bs-json');
        const season = button.getAttribute('data-bs-season');
        <?php
        $userDirs = array_keys($raceAnalysisFiles);
        array_walk($userDirs, function (&$userDir) {
            $userDir = addcslashes($userDir, '\\');
        });
        ?>
        const userDirs = ['<?= implode("','", $userDirs) ?>'];
        const jsonFiles = [];
        const datasets = [];
        // https://html-color.codes/
        const rgbSet = [
            [255, 0, 0], // red
            [128, 0, 128], // purple
            [255, 165, 0], // orange

            [238, 210, 2], // yellow (safety)

            [0, 0, 128], // navy
            // 7
            [255, 99, 71], // red (tomato)
            [147, 112, 219], // purple (medium)
            [255, 140, 0], // orange (dark)

            [255, 225, 53], // yellow (banana)

            [0, 0, 205], // navy (medium blue)
            // 14
            [233, 150, 122], // red (dark salmon)
            [123, 104, 238], // purple (medium slate blue)
            [255, 179, 71], // orange (pastel orange)

            [240, 225, 48], // yellow (dandelion)

            [50, 74, 178], // navy (violet blue)
            // 21
        ];
        const qRgbSet = [
            [0, 128, 0], // green
            [0, 0, 255], // blue
            [144, 238, 144], // green (light)
            [65, 105, 225], // blue (royal)
            [143, 188, 143], // green (dark sea green)
            [100, 149, 237], // blue (cornflower blue)
        ];

        const updateJsonFiles = (data) => jsonFiles.push(data);
        const drawChart = () => {
            const xValues = [];

            jsonFiles.forEach((data, idx) => {
                const laps = data.race.laps;
                let yValues = [];
                let inLap = outLap = 0.0;

                let pittedOnPrevLap = false;

                const getQualiSeconds = (qTime) => {
                    const qTimeSlots = qTime.replaceAll('s', '').split(':');
                    let qSeconds = 0.0;
                    if (qTimeSlots.length === 2) {
                        qSeconds = parseInt(qTimeSlots[0]) * 60 + parseFloat(qTimeSlots[1]);
                    } else {
                        qSeconds = parseFloat(qTimeSlots[0]);
                    }
                    return qSeconds;
                }

                const q1Seconds = getQualiSeconds(data.q1.time);
                const q2Seconds = getQualiSeconds(data.q2.time);

                laps.forEach((el, index) => {
                    let timeSlots = el.time.split(':'); // split it at the colons
                    let seconds = 0.0;
                    if (timeSlots.length === 2) {
                        seconds = parseInt(timeSlots[0]) * 60 + parseFloat(timeSlots[1]);
                    } else {
                        seconds = parseFloat(timeSlots[0]);
                    }

                    if (timeSlots[0] !== '-') {
                        if (index > 0 && xValues.length < (laps.length - 1)) {
                            xValues.push(index);
                        }
                        if (el.events.includes('Pit') || pittedOnPrevLap) {
                            yValues.push(null);
                            pittedOnPrevLap = !pittedOnPrevLap;
                        } else {
                            yValues.push(seconds);
                        }
                    }
                });

                const rgb = rgbSet[idx];
                const q1Rgb = qRgbSet[idx * 2];
                const q2Rgb = qRgbSet[idx * 2 + 1];

                datasets.push({
                    label: data.manager,
                    backgroundColor: `rgba(${rgb[0]}, ${rgb[1]}, ${rgb[2]}, 1.0)`,
                    borderColor: `rgba(${rgb[0]}, ${rgb[1]}, ${rgb[2]}, 0.5)`,
                    data: yValues,
                    laps: data.race.laps,
                });
                datasets.push(
                    {
                        label: 'Q1 ' + data.manager,
                        backgroundColor: `rgba(${q1Rgb[0]}, ${q1Rgb[1]}, ${q1Rgb[2]}, 1.0)`,
                        borderColor: `rgba(${q1Rgb[0]}, ${q1Rgb[1]}, ${q1Rgb[2]}, 0.5)`,
                        data: new Array(xValues.length).fill(q1Seconds),
                        time: 'Q1 ' + data.q1.time.replaceAll('s', ''),
                    }
                );
                datasets.push(
                    {
                        label: 'Q2 ' + data.manager,
                        backgroundColor: `rgba(${q2Rgb[0]}, ${q2Rgb[1]}, ${q2Rgb[2]}, 1.0)`,
                        borderColor: `rgba(${q2Rgb[0]}, ${q2Rgb[1]}, ${q2Rgb[2]}, 0.5)`,
                        data: new Array(xValues.length).fill(q2Seconds),
                        time: 'Q2 ' + data.q2.time.replaceAll('s', ''),
                    }
                );
            });

            if (charts.length > 0) {
                charts.pop().destroy();
            }

            let newChart = new Chart("chart", {
                type: "line",
                data: {
                    labels: xValues,
                    datasets: datasets
                },
                options: {
                    scales: {
                        x: {
                            beginAtZero: false,
                            title: {
                                display: true,
                                text: 'Laps'
                            },
                            ticks: {
                                color: 'rgb(0,0,0)'
                            }
                        },
                        y: {
                            beginAtZero: false,
                            title: {
                                display: true,
                                text: 'Lap Time'
                            },
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
                    },
                    plugins: {
                        legend: {
                            position: 'left',
                            align: 'start',
                            fullSize: true,
                        },
                        title: {
                            display: true,
                            text: 'Click on rectangles to show/hide lap chart'
                        },
                        tooltip: {
                            callbacks: {
                                label: ((item, data) => {
                                    if (!!item.dataset.laps) {
                                        return item.dataset.laps[item.dataIndex + 1].time;
                                    }
                                    if (!!item.dataset.time) {
                                        return item.dataset.time;
                                    }
                                })
                            }
                        }
                    }
                }
            });

            charts.push(newChart);
        };

        const loadNextJsonFile = () => {
            const userDir = userDirs.shift();
            if (userDir === undefined) {
                drawChart();
                return;
            }

            const jsonUrl = userDir + '/' + season + '/' + jsonFile + '?_=' + new Date().getTime();
            $.getJSON(jsonUrl)
                .done((data) => {
                    data['manager'] = userDir.replace(/^seasons[\\/]/, '');
                    updateJsonFiles(data);

                    loadNextJsonFile();
                })
                .fail((err) => {
                    loadNextJsonFile();
                });
        };
        loadNextJsonFile();
    });
</script>
