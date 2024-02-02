<div class="card card-body">
    <p>Median Elite Race Time <b><?= $medianHours.'h '.$medianMinutes.'m'?></b></p>
    <table class="table table-sm table-striped table-hover">
        <tr>
            <th>Race Analysis</th>
            <th>Elite Race Time</th>
            <th>Energy</th>
            <th>Dry/Wet</th>
            <th>Rain</th>
            <th>Con</th>
            <th>Tal</th>
            <th>Agg</th>
            <th>Exp</th>
            <th>Tei</th>
            <th>Sta</th>
            <th>Wei</th>
        </tr>
        <?php foreach ($filteredHistory as $race) :?>
        <tr>
            <td>
                <a href="<?= $race['file']??''?>" target="_blank"
                >S<?= $race['season']?>R<?= $race['race']?></a>
            </td>
            <td><?= $race['time']?></td>
            <?php if (isset($race['driver'])) :?>
            <td>
                <?= $race['driver']['energy']['before_race']-$race['driver']['energy']['after_race']?>&#65130;
                <div>
                    <small class="text-muted">
                    <?= $race['driver']['energy']['before_race']
                        .'&rarr;'
                        .$race['driver']['energy']['after_race']
                    ?>
                    </small>
                </div>
            </td>
            <td><?= $race['ct_dry'].'/'.$race['ct_wet']?></td>
            <td><?= isset($race['wet_na'])?'n/a':(isset($race['wet'])?$race['wet'].'&#65130;':'-')?></td>
            <td><?= $race['driver']['CON']?></td>
            <td><?= $race['driver']['TAL']?></td>
            <td><?= $race['driver']['AGG']?></td>
            <td><?= $race['driver']['EXP']?></td>
            <td><?= $race['driver']['TEI']?></td>
            <td><?= $race['driver']['STA']?></td>
            <td><?= $race['driver']['WEI']?></td>
            <?php else :?>
            <td colspan="10"></td>
            <?php endif;?>
        </tr>
        <?php endforeach;?>
    </table>
    <div class="text-end text-muted">Generated in ~<?= $elapsed?>s</div>
</div>
