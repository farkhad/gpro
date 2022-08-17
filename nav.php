<?php
function linkAttr($page, $currentPage)
{
    if ($currentPage === $page) {
        return 'class="nav-link active" aria-current="page"';
    }
    return 'class="nav-link" href="' . $page . '.php"';
}
?>
<!-- https://getbootstrap.com/docs/5.1/components/navs-tabs/#tabs -->
<ul class="nav nav-tabs mb-3">
    <li class="nav-item">
        <a <?=linkAttr('index', $page)?>>Home</a>
    </li>
    <li class="nav-item">
        <a <?=linkAttr('postrace', $page)?>>Download Race Analysis</a>
    </li>
    <li class="nav-item">
        <a <?=linkAttr('market', $page)?>>Download Market Database</a>
    </li>
    <li class="nav-item">
        <a <?=linkAttr('find', $page)?>>Find Best Driver</a>
    </li>
</ul>
