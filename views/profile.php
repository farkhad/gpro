<div class="row">
    <div class="col">
        <?php if (!count($marketFiles)) : ?>
            <p class="text-danger">There are no market database files on your computer.</p>
            You may <a href="market.php">download</a> latest market database now.</p>
        <?php endif; ?>
        <form method="GET">
            <div class="row w-75 mb-3">
                <div class="col">
                    <label for="link">Copy/Paste Driver's Profile Link</label>
                    <input class="form-control form-control-sm" type="text" id="link" name="link" placeholder="https://gpro.net/DriverProfile.asp?ID=83" oninput="this.value !== '' ? this.form.id.value = Number(this.value.split('?ID=')[1]) : ''">
                </div>
            </div>
            <div class="row w-50">
                <div class="col"><label for="id">Driver's ID</label></div>
                <div class="col">
                    <input class="form-control form-control-sm" type="number" name="id" id="id" value="<?= $driverId ?? '' ?>">
                </div>
                <div class="col">
                    <button class="btn btn-primary btn-sm">Find</button>
                </div>
            </div>
            <p class="mt-3 mb-0">
                You have got <b><?= count($marketFiles) ?></b> market database files on your computer.
            </p>
        </form>
    </div>
    <div class="col w-25">
        <?php if (!empty($profile)) : ?>
            Season <?= $historySeason?>, Race <?= $historyRace?>
            <p class="text-success">
                <b><a href="https://gpro.net/gb/DriverProfile.asp?ID=<?= $profile['ID'] ?>" target="_blank"><?= $profile['NAME'] ?></a></b> (<?= $profile['AGE'] ?>)
                from database <b><?= $marketFile ?></b>
            </p>
            <table class="table table-striped table-sm font-monospace">
                <?php foreach ($profile as $key => $value) : ?>
                    <?php
                    if ($key === 'FAV') {
                        $value = join(', ', $value);
                    }
                    if ($key === 'SAL' || $key === 'FEE') {
                        $value = number_format($value, 0, null, '.');
                    }
                    ?>
                    <tr>
                        <td class="w-25"><?= $key ?></td>
                        <td><?= $value ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <div class="text-end text-muted"><small>Found in ~<?= $timeSpent?>s.</small></div>
        <?php elseif (!empty($driverId)) : ?>
            <p class="text-danger">Driver's profile with ID <?= $driverId ?> not found.</p>
        <?php endif; ?>
    </div>
</div>
