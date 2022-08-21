<?php

/**
 *
 * Find Driver's Profile by ID against downloaded market database files
 */

// Perform search against limited number of market files
const MARKET_FILES_LIMIT = 200;

$marketFolder = 'market' . DIRECTORY_SEPARATOR;
$marketFiles = glob($marketFolder . '[!TD]*.php');
rsort($marketFiles);
$marketFiles = array_slice($marketFiles, 0, MARKET_FILES_LIMIT);

$profile = [];
$marketFile = '';

if (!empty($_GET['id'])) {
    $driverId = (int) $_GET['id'];

    foreach ($marketFiles as $marketFile) {
        $content = file_get_contents($marketFile);
        if (false !== strpos($content, "'ID' => $driverId,")) {
            $drivers = require $marketFile;
            foreach ($drivers['drivers'] as $driver) {
                if ($driver['ID'] === $driverId) {
                    $profile = $driver;
                    break;
                }
            }
            break;
        }
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Driver's Profile <?= $profile['NAME'] ?? '' ?></title>
    <link rel="shortcut icon" href="img/favicon.png" type="image/png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-gH2yIJqKdNHPEq0n4Mqa/HGKIhSkIHeL5AyhkYV8i59U5AR6csBvApHHNl/vI1Bx" crossorigin="anonymous">
    <script defer src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <script async src="https://kit.fontawesome.com/f711a4bfbd.js" crossorigin="anonymous"></script>
</head>

<body class="m-5">
    <?php
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
                        <label for="link">Copy/Paste Driver's Profile Link</label>
                        <input class="form-control form-control-sm" type="text" id="link" name="link" placeholder="https://www.gpro.net/DriverProfile.asp?ID=83" oninput="this.value !== '' ? this.form.id.value = Number(this.value.split('?ID=')[1]) : ''">
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
                    Search will be performed against maximum of <b><?= MARKET_FILES_LIMIT ?></b> latest market database files.
                </p>
                <p>You have got <b><?= count($marketFiles) ?></b> market database files on your computer.
                </p>
            </form>
        </div>
        <div class="col w-25">
            <?php if (!empty($profile)) : ?>
                <p class="text-success">
                    <b><a href="https://www.gpro.net/gb/DriverProfile.asp?ID=<?= $profile['ID'] ?>" target="_blank"><?= $profile['NAME'] ?></a></b> (<?= $profile['AGE'] ?>)
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
