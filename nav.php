<?php
$page = pathinfo($_SERVER['SCRIPT_FILENAME'], PATHINFO_FILENAME);

function linkAttr($page, $currentPage)
{
    if ($currentPage === $page) {
        return 'class="nav-link active" aria-current="page"';
    }
    return 'class="nav-link" href="' . $page . '.php"';
}

?>
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a <?= linkAttr('index', $page) ?>><i class="fa-solid fa-house"></i> Home</a>
    </li>
    <li class="nav-item">
        <a <?= linkAttr('find', $page) ?>><i class="fa-solid fa-magnifying-glass"></i> Find Best Driver</a>
    </li>
    <li class="nav-item">
        <a <?= linkAttr('profile', $page) ?>><i class="fa-solid fa-user"></i> Driver's Profile</a>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link <?= in_array($page, ['postrace', 'market']) ? 'active' : '' ?> dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false"><i class="fa-solid fa-download"></i> Download</a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="postrace.php">Latest Race Analysis</a></li>
            <li><a class="dropdown-item" href="market.php">Latest Market Database</a></li>
        </ul>
    </li>
</ul>
