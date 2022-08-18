<?php
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
        <a <?= linkAttr('index', $page) ?>>Home</a>
    </li>
    <li class="nav-item">
        <a <?= linkAttr('find', $page) ?>>Find Best Driver</a>
    </li>
    <li class="nav-item">
        <a <?= linkAttr('profile', $page) ?>>Driver's Profile</a>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-expanded="false">Download</a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="postrace.php">Latest Race Analysis</a></li>
            <li><a class="dropdown-item" href="market.php">Latest Market Database</a></li>
        </ul>
    </li>
</ul>
