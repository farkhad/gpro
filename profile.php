<?php

/**
 * Find Driver's Profile by ID among market database files
 */
$marketFolder = 'market' . DIRECTORY_SEPARATOR;
$marketFiles = glob($marketFolder . '*.php');
$profile = [];
$marketFile = '';

if (!empty($_GET['id'])) {
    $driverId = (int) $_GET['id'];

    rsort($marketFiles);
    foreach ($marketFiles as $marketFile) {
        $content = file_get_contents($marketFile);
        if (false !== strpos($content, "'ID' => $driverId")) {
            $drivers = require $marketFile;
            foreach ($drivers['drivers'] as $driver) {
                if ($driver['ID'] === $driverId) {
                    $profile = $driver;
                    break 2;
                }
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GPRO Home Server</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
</head>

<body class="m-5">
    <?php
    $page = pathinfo(__FILE__, PATHINFO_FILENAME);
    include 'nav.php';
    ?>
    <div class="row">
        <div class="col">
            <?php if (!count($marketFiles)) : ?>
                <p class="text-danger">There are no market database files on your computer.</p>
                You may <a href="market.php">download</a> latest market database now.</p>
            <?php endif; ?>
            <form method="GET">
                <div class="row w-75 mb-3">
                    <div class="col">
                        <label for="link">Copy/Paste GPRO Driver's Profile Link</label>
                        <input class="form-control form-control-sm" type="text" id="link" name="link" placeholder="https://www.gpro.net/gb/DriverProfile.asp?ID=83" oninput="this.value !== '' ? this.form.id.value = Number(this.value.split('?ID=')[1]) : ''">
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
                <p class="mt-3">
                    Search will be performed among <b><?= count($marketFiles) ?></b> market database files.
                </p>
            </form>
        </div>
        <div class="col w-25">
            <?php if (!empty($profile)) : ?>
                <p class="text-success">
                    <b><?= $driver['NAME'] ?></b> (<?= $driver['AGE'] ?>)
                    from database <b><?= $marketFile ?></b>
                </p>
                <table class="table table-striped table-sm font-monospace">
                    <?php foreach ($profile as $key => $value) : ?>
                        <?php
                        if ($key === 'FAV') {
                            $value = join(', ', $value);
                        }
                        ?>
                        <tr>
                            <td class="w-25"><?= $key ?></td>
                            <td><?= $value ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php elseif (!empty($driverId)) : ?>
                <p class="text-danger">Driver's profile with ID <?= $driverId ?> not found.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
