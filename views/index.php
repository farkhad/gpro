<div class="row">
    <div class="col">
    <?php foreach ($raceAnalysisFiles as $userDir => $seasons) : ?>
        <h3><?= basename($userDir) ?></h3>
        <p>
            <i class="fa-solid fa-circle-info"></i>
            All your race data files can be directly accessed using Windows Explorer on your computer at
            <b><?= GPRO_HOME_SERVER_FOLDER.DIRECTORY_SEPARATOR.$userDir?></b>
        </p>
        <?php $curSeason = array_key_first($seasons); ?>
        <!-- Season Files -->
        <div class="row mb-3">
            <?php foreach ($seasons as $season => $seasonRaceAnalysisFiles) : ?>
            <?php $counter = isset($counter) ? $counter+1 : 0;?>
            <div class="col">
                <button
                    class="btn btn-info mb-3 w-100"
                    data-bs-toggle="collapse"
                    data-bs-target="#season<?= $season?>"
                    aria-expanded="false"
                    aria-controls="season<?= $season?>"
                >
                Season <?= $season?> Files
                </button>
                <div class="collapse multi-collapse" id="season<?= $season?>">
                    <div class="card card-body">
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
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button
            class="btn btn-info mb-3 w-100"
            data-bs-toggle="collapse"
            data-bs-target="#next-races-overview"
            aria-expanded="false"
            aria-controls="next-races-overview"
        >
            Next Race Overview
        </button>
        <div class="collapse multi-collapse mb-3" id="next-races-overview">
            <div class="text-center">
                <strong role="status">Retrieving data from gpro.net</strong>
                <div class="spinner-border spinner-border-sm ms-auto" aria-hidden="true"></div>
            </div>
        </div>

        <!-- Season Dashboard -->
        <table class="table table-hover table-striped">
            <!-- Season Race Numbers -->
            <tr>
                <th scope="col"><i>Season <?= $curSeason?></i></th>
                <?php
                for ($n = 1; $n < 18; $n++) :
                    $raceId = 'S'.$curSeason.'R'.$n;
                    $trackId = $seasonCalendar['tracks'][$n-1]['track_id'];
                    $track = $trackDetails[$trackId];
                    $pos = null;
                    $raceBtnColor = 'btn-light';
                    $racePosColor = 'text-secondary';
                    if (isset($seasonRaces[$raceId])) {
                        $pos = (int) $seasonRaces[$raceId]['race']['finish'];
                        if ($pos === 1) {
                            $raceBtnColor = 'btn-success';
                            $racePosColor = 'text-light';
                        }
                    }
                ?>
                <td title="<?= $track['name'].($pos ? ' &mdash; P'.$pos : '')?>">
                    <a
                        class="btn btn-sm <?= $raceBtnColor?> fw-bold"
                        data-bs-toggle="collapse"
                        href="#race<?= $raceId?>"
                        aria-expanded="false"
                        aria-controls="race<?= $raceId?>"
                    >
                        R<?= $n.(null !== $pos ? '<sup class="'.$racePosColor.'">'.$pos.'</sup>' : '')?>
                    </a>
                    <div class="mt-1 collapse text-nowrap" id="race<?= $raceId?>">
                        <span title="<?=$track['country']?>" class="fi fi-<?= strtolower($countryCodes[$track['country']])?>"></span
                        >&nbsp;<?= $track['name']?>
                        <div title="P/H/A">
                            <?= $track['power'].'/'.$track['handling'].'/'.$track['acceleration']?>
                        </div>
                    </div>
                </th>
                <?php endfor; ?>
            </tr>
            <!-- Season Weather -->
            <tr>
                <th scope="row">Weather</th>
                <?php
                for ($n = 1; $n < 18; $n++) {
                    $raceId = 'S'.$curSeason.'R'.$n;
                    if (!isset($seasonRaces[$raceId])) {
                        echo '<td></td>'.PHP_EOL;
                        continue;
                    }
                    $laps = $seasonRaces[$raceId]['race']['laps'];
                    // remove lap 0
                    array_shift($laps);

                    $wetLaps = array_filter($laps, fn ($lap) => 'W' === $lap['weather']);
                    $nbWetLaps = count($wetLaps);
                    $nbLaps = count($laps);
                    $percentageWetLaps = round($nbWetLaps*100/$nbLaps);

                    $temperatures = array_column($laps, 'temp');

                    $nbTemperatures = count($temperatures);
                    $maxTemperature = max($temperatures);
                    $minTemperature = min($temperatures);

                    sort($temperatures);
                    $middleValue = floor(($nbTemperatures-1)/2);
                    if ($nbTemperatures % 2) {
                        $medianTemperature = $temperatures[$middleValue];
                    } else {
                        $low = $temperatures[$middleValue];
                        $high = $temperatures[$middleValue+1];
                        $medianTemperature = (($low+$high)/2);
                    }

                    $color = 'text-bg-warning';
                    if ($medianTemperature > 29) {
                        $color = 'text-bg-danger';
                    }
                    if ($medianTemperature < 19) {
                        $color = 'text-bg-primary';
                    }
                    echo '<td><span title="Median Value of Temperatures" class="badge p-2 '.$color.'">'.round($medianTemperature).'°</span>'
                        .'<div><small class="text-muted">'.$minTemperature.'°-'.$maxTemperature.'°</small></div>'
                        .
                        ($nbWetLaps > 0
                            ? '<div title="Rain '.$nbWetLaps.' laps of '.$nbLaps.'"><i class="fa-solid fa-cloud-showers-heavy"></i>&nbsp;<small class="text-muted">'.$percentageWetLaps.'%</small></div>'
                            : ''
                        )
                        .'</td>'.PHP_EOL
                    ;
                }
                ?>
            </tr>
            <!-- Energy & Risks Used -->
            <tr>
                <th scope="row">
                    Energy Used
                    <div><small class="text-muted">Before&rarr;After</small></div>
                    <div><small class="text-muted">Risk Dry/Wet</small></div>
                </th>
                <?php
                for ($n = 1; $n < 18; $n++) {
                    $raceId = 'S'.$curSeason.'R'.$n;
                    if (!isset($seasonRaces[$raceId])) {
                        echo '<td></td>'.PHP_EOL;
                        continue;
                    }
                    $race = $seasonRaces[$raceId]['race'];
                    $driver = $seasonRaces[$raceId]['driver'];
                    $ctDry = $race['ct_dry'];
                    $ctWet = $race['ct_wet'];
                    $energy = $driver['energy'];
                    $energyUsed = $energy['before_race']-$energy['after_race'];

                    echo '<td>'.$energyUsed.'&#65130;'
                        .'<div><small class="text-muted">'
                        .$energy['before_race'].'&rarr;'
                        .
                        (
                            $energy['after_race'] === 0
                            ? '<span class="text-danger">0</span>'
                            : $energy['after_race']
                        )
                        .'</small></div>'
                        .'<div><small class="text-muted">'.$race['ct_dry'].'/'.$race['ct_wet'].'</small></div>'
                        .'</td>'.PHP_EOL
                    ;
                }
                ?>
            </tr>
            <!-- Energy Recovered -->
            <tr>
                <th scope="row">Recovered</th>
                <?php
                $prevEnergyLeft = null;
                for ($n = 1; $n < 18; $n++) {
                    $raceId = 'S'.$curSeason.'R'.$n;
                    if (!isset($seasonRaces[$raceId])) {
                        echo '<td></td>'.PHP_EOL;
                        continue;
                    }
                    $race = $seasonRaces[$raceId]['race'];
                    $driver = $seasonRaces[$raceId]['driver'];
                    $energy = $driver['energy'];
                    $energyUsed = $energy['before_race']-$energy['after_race'];
                    $energyUsedInQuali = $energy['before_q1']-$energy['after_q1']
                        +$energy['before_q2']-$energy['after_q2']
                    ;

                    echo '<td>'
                        .'<span title="Energy Recovered + Used In Qualification">'
                        .
                        (
                            isset($prevEnergyLeft)
                            ? ($energy['before_race']-$prevEnergyLeft)
                                .'+'.$energyUsedInQuali.'&#65130;'
                            : ''
                        )
                        .'</span><div>'
                        .
                        (
                            isset($prevEnergyLeft)
                            ? '<small class="text-muted" title="Energy After R'.($n-1).' &rarr; Before R'.$n.', %">'.$prevEnergyLeft.'&rarr;'.$energy['before_race'].'</small>'
                            : ''
                        )
                        .'</div></td>'.PHP_EOL
                    ;

                    $prevEnergyLeft = $energy['after_race'];
                }
                ?>
            </tr>
            <!-- Balance -->
            <tr>
                <th scope="row">
                    Balance, M
                    <div><small class="text-muted">Earnings</small></div>
                </th>
                <?php
                $prevFinances = null;
                for ($n = 1; $n < 18; $n++) {
                    $raceId = 'S'.$curSeason.'R'.$n;
                    if (!isset($seasonRaces[$raceId])) {
                        echo '<td></td>'.PHP_EOL;
                        continue;
                    }

                    $finances = $seasonRaces[$raceId]['finances'];
                    $diff = round(($finances['balance']-($prevFinances['balance']??0))/1000000,1);

                    $diff = 0;
                    if (isset($prevFinances['balance'])) {
                        $diff = $finances['balance']-$prevFinances['balance'];
                    }
                    $diffColor = 'text-secondary';
                    if ($diff > 0) {
                        $diff = '+'.round($diff/1000000,1);
                        $diffColor = 'text-success';
                    } elseif ($diff < 0) {
                        $diff = round($diff/1000000,1);
                        $diffColor = 'text-danger';
                    }

                    echo '<td>'.round($finances['balance']/1000000,1)
                        .($diff ? '<small class="'.$diffColor.'">'.$diff.'</small>': '')
                        .'<div><small class="text-muted">+'.round($finances['total']/1000000,1).'</small></div>'
                        .'</td>'
                    ;

                    $prevFinances = $finances;
                }
                ?>
            </tr>
            <!-- Sponsors -->
            <?php if (isset($sponsors[$userDir])) :?>
            <tr>
                <th scope="col" colspan="18">Sponsors</th>
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
                <td>Total Gained</td>
                <?php for ($n = 1; $n < 18; $n++) : ?>
                    <?php if (isset($totals[$n])) : ?>
                    <td class="text-success">+<?= $totals[$n] ?></td>
                    <?php else : ?>
                    <td></td>
                    <?php endif; ?>
                <?php endfor; ?>
            </tr>
            <?php endif; ?>
            <!-- Driver -->
            <tbody class="table-group-divider">
            <tr>
                <th scope="row" colspan="18">Driver</th>
            </tr>
            <?php
            $driverSkills = [
                "OA",
                "CON",
                "TAL",
                "AGG",
                "EXP",
                "TEI",
                "STA",
                "CHA",
                "MOT",
                "REP",
                "WEI",
            ];
            foreach ($driverSkills as $driverSkill) :
            ?>
            <tr>
                <td><?= ucfirst(strtolower($driverSkill))?></td>
                <?php
                $prevDriver = [];
                for ($n = 1; $n < 18; $n++) {
                    $raceId = 'S'.$curSeason.'R'.$n;
                    if (!isset($seasonRaces[$raceId])) {
                        echo '<td></td>'.PHP_EOL;
                        continue;
                    }

                    $driver = $seasonRaces[$raceId]['driver'];

                    $diffAfterRace = $driver['diff'][$driverSkill] ?? 0;
                    $driverSkillValue = $driver[$driverSkill]+$diffAfterRace;
                    if ($diffAfterRace > 0) {
                        $diffAfterRace = '+'.$diffAfterRace;
                    }

                    $diff = 0;
                    if (isset($prevDriver[$driverSkill])) {
                        $diff = $driver[$driverSkill]+($driver['diff'][$driverSkill] ?? 0)
                            -($prevDriver[$driverSkill]+($prevDriver['diff'][$driverSkill] ?? 0))
                        ;
                    }
                    $diffColor = 'text-secondary';
                    if ($diff > 0) {
                        $diff = '+'.$diff;
                        $diffColor = 'text-success';
                    } elseif ($diff < 0) {
                        $diffColor = 'text-danger';
                    }

                    echo '<td title="Before Race '.$driver[$driverSkill]
                        .' After Race '.$diffAfterRace.'">'
                        .$driverSkillValue
                        .($diff ? '<small class="'.$diffColor.'">'.$diff.'</small>': '')
                        .'</td>'
                    ;
                    $prevDriver = $driver;
                }
                ?>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <!-- Staff & Facilities -->
            <tbody class="table-group-divider">
            <tr>
                <th scope="row" colspan="18">Staff & Facilities</th>
            </tr>
            <tr>
                <td>Overall</td>
                <?php
                $prevSf = [];
                for ($n = 1; $n < 18; $n++) {
                    $raceId = 'S'.$curSeason.'R'.$n;
                    if (!isset($seasonRaces[$raceId])) {
                        echo '<td></td>'.PHP_EOL;
                        continue;
                    }

                    $sf = $seasonRaces[$raceId]['sf'];
                    $diff = 0;
                    if (isset($prevSf['overall'])) {
                        $diff = $sf['overall']-$prevSf['overall'];
                    }
                    $diffColor = 'text-secondary';
                    if ($diff > 0) {
                        $diff = '+'.$diff;
                        $diffColor = 'text-success';
                    } elseif ($diff < 0) {
                        $diffColor = 'text-danger';
                    }

                    echo '<td>'.$sf['overall']
                        .($diff ? '<small class="'.$diffColor.'">'.$diff.'</small>': '')
                        .'</td>'
                    ;
                    $prevSf = $sf;
                }
                ?>
            </tr>
            <tr>
                <td>Salary, K</td>
                <?php
                $prevSf = [];
                for ($n = 1; $n < 18; $n++) {
                    $raceId = 'S'.$curSeason.'R'.$n;
                    if (!isset($seasonRaces[$raceId])) {
                        echo '<td></td>'.PHP_EOL;
                        continue;
                    }

                    $sf = $seasonRaces[$raceId]['sf'];
                    $diff = 0;
                    if (isset($prevSf['salary'])) {
                        $diff = $sf['salary']-$prevSf['salary'];
                    }
                    $diffColor = 'text-secondary';
                    if ($diff > 0) {
                        $diff = '+'.round($diff/1000, 1);
                        $diffColor = 'text-success';
                    } elseif ($diff < 0) {
                        $diff = round($diff/1000, 1);
                        $diffColor = 'text-danger';
                    }

                    echo '<td title="'.$sf['salary'].'">'.round($sf['salary']/1000, 1)
                        .($diff ? '<small class="'.$diffColor.'">'.$diff.'</small>': '')
                        .'</td>'
                    ;
                    $prevSf = $sf;
                }
                ?>
            </tr>
            <tr>
                <td>Maintenance, K</td>
                <?php
                $prevSf = [];
                for ($n = 1; $n < 18; $n++) {
                    $raceId = 'S'.$curSeason.'R'.$n;
                    if (!isset($seasonRaces[$raceId])) {
                        echo '<td></td>'.PHP_EOL;
                        continue;
                    }

                    $sf = $seasonRaces[$raceId]['sf'];
                    $diff = 0;
                    if (isset($prevSf['maintenance'])) {
                        $diff = $sf['maintenance']-$prevSf['maintenance'];
                    }
                    $diffColor = 'text-secondary';
                    if ($diff > 0) {
                        $diff = '+'.round($diff/1000, 1);
                        $diffColor = 'text-success';
                    } elseif ($diff < 0) {
                        $diff = round($diff/1000, 1);
                        $diffColor = 'text-danger';
                    }

                    echo '<td title="'.$sf['maintenance'].'">'.round($sf['maintenance']/1000,1)
                        .($diff ? '<small class="'.$diffColor.'">'.$diff.'</small>': '')
                        .'</td>'
                    ;
                    $prevSf = $sf;
                }
                ?>
            </tr>
            </tbody>
            <?php
            $staffKeys = [
                'exp' => 'Experience',
                'mot' => 'Motivation',
                'tech' => 'Technical skill',
                'stress' => 'Stress',
                'con' => 'Concentration',
                'eff' => 'Efficiency',
            ];
            $facilitiesKeys = [
                'commercial' => 'Commercial',
                'pitstop' => 'Pitstop',
                'wind' => 'Windtunnel',
                'rd_workshop' => 'R&D workshop',
                'rd_design' => 'R&D design center',
                'eng_workshop' => 'Engineering workshop',
                'alloy' => 'Alloy and chemical lab',
            ];

            $sfKeys = [
                'staff' => $staffKeys,
                'facilities' => $facilitiesKeys,
            ];

            foreach ($sfKeys as $sfKey => $sfAttrs) :
                if ('facilities' === $sfKey) :
            ?>
            <tbody class="table-group-divider">
            <tr>
                <td>Avg. Training Lvl</td>
                <?php
                $prevSf = [];
                for ($n = 1; $n < 18; $n++) {
                    $raceId = 'S'.$curSeason.'R'.$n;
                    if (!isset($seasonRaces[$raceId])) {
                        echo '<td></td>'.PHP_EOL;
                        continue;
                    }

                    $sf = $seasonRaces[$raceId]['sf'];
                    $diff = 0;
                    if (isset($prevSf['training'])) {
                        $diff = $sf['training']-$prevSf['training'];
                    }
                    $diffColor = 'text-secondary';
                    if ($diff > 0) {
                        $diff = '+'.$diff;
                        $diffColor = 'text-success';
                    } elseif ($diff < 0) {
                        $diffColor = 'text-danger';
                    }

                    echo '<td>'.$sf['training']
                        .($diff ? '<small class="'.$diffColor.'">'.$diff.'</small>': '')
                        .'</td>'
                    ;
                    $prevSf = $sf;
                }
                ?>
            </tr>
            </tbody>
            <?php endif; ?>
            <tbody class="table-group-divider">
            <?php
                foreach ($sfAttrs as $staffKey => $staffAttr) :
            ?>
            <tr>
                <td><?= $staffAttr?></td>
                <?php
                $prevSf = [];
                for ($n = 1; $n < 18; $n++) {
                    $raceId = 'S'.$curSeason.'R'.$n;
                    if (!isset($seasonRaces[$raceId])) {
                        echo '<td></td>'.PHP_EOL;
                        continue;
                    }

                    $sf = $seasonRaces[$raceId]['sf'][$sfKey];
                    $diff = 0;
                    if (isset($prevSf[$staffKey])) {
                        $diff = $sf[$staffKey]-$prevSf[$staffKey];
                    }
                    $diffColor = 'text-secondary';
                    if ($diff > 0) {
                        $diff = '+'.$diff;
                        $diffColor = 'text-success';
                    } elseif ($diff < 0) {
                        $diffColor = 'text-danger';
                    }

                    echo '<td>'.$sf[$staffKey]
                        .($diff ? '<small class="'.$diffColor.'">'.$diff.'</small>': '')
                        .'</td>'
                    ;
                    $prevSf = $sf;
                }
                ?>
            </tr>
            <?php endforeach; ?>
            </tbody>
            <?php endforeach; ?>
        </table>
    <?php endforeach; ?>
    </div>
</div>

<div class="card card-body">
    <?php if (!count($marketFiles)) : ?>
        Market files not found. <a href="market.php">Download</a> latest drivers market database file.
    <?php else : ?>
        Latest <?= MARKET_FILES_LIMIT ?> Market Files
        <ol>
            <?php foreach ($marketFiles as $marketFile) : ?>
                <li><?= $marketFile ?></li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>

    <?php if (!count($marketFilesTechDirectors)) : ?>
        Market files not found. <a href="market.php">Download</a> latest tech directors market database file.
    <?php else : ?>
        Latest <?= MARKET_FILES_LIMIT ?> Tech Directors Market Files
        <ol>
            <?php foreach ($marketFilesTechDirectors as $marketFile) : ?>
                <li><?= $marketFile ?></li>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
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

    function xhrListener() {
        const nextRacesOverview = document.querySelector('#next-races-overview');
        nextRacesOverview.querySelector('div').remove()

        nextRacesOverview.insertAdjacentHTML('beforeend', this.responseText);
    }

    function loadNextRacesOverview() {
        const xhr = new XMLHttpRequest();
        xhr.addEventListener('load', xhrListener);
        xhr.open('GET', 'next-races-overview.php');
        xhr.send();
    }

    loadNextRacesOverview();
</script>
