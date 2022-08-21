<?php

function getPracticeLaps($postraceHtml)
{
    $pattern = '|<div id="PracticeData".+?<table class="styled borderbottom flag".+?>.+?<table.+?class="styled borderbottom".+?>(.+?)</table>|is';

    // Practice Laps can be empty
    $practiceLaps = [];
    if (false === preg_match($pattern, $postraceHtml, $matches)) {
        return $practiceLaps;
    }

    $html = $matches[1];
    $matches = [];

    $pattern = '|<tr class="pointerhand".+?'
        . '<td.+?>(?<lap>.+?)</td>.+?'
        . '<td.+?>(?<gross>.+?)</td>.+?'
        . '<td.+?>(?<dm>.+?)</td>.+?'
        . '<td.+?>(?<net>.+?)</td>.+?'
        . '<td.+?>(?<fw>.+?)</td>.+?'
        . '<td.+?>(?<rw>.+?)</td>.+?'
        . '<td.+?>(?<eng>.+?)</td>.+?'
        . '<td.+?>(?<bra>.+?)</td>.+?'
        . '<td.+?>(?<gear>.+?)</td>.+?'
        . '<td.+?>(?<susp>.+?)</td>.+?'
        . '<td.+?>(?<tyres>.+?)</td>.+?'
        . '<td.+?>.+?\.innerHTML\=\'<br>(?<comm>.+?)\';.+?</td>.+?'
        . '</tr>|is';
    if (false !== preg_match_all($pattern, $html, $matches)) {
        foreach ($matches['lap'] as $i => $lap) {
            $comments = explode('<br>', trim($matches['comm'][$i]));
            array_walk($comments, fn (&$element) => $element = strip_tags($element));

            $practiceLaps[$i] = [
                'gross' => trim(strip_tags($matches['gross'][$i])),
                'net' => trim(strip_tags($matches['net'][$i])),
                'dm' => trim(strip_tags($matches['dm'][$i])),
                'fw' => trim($matches['fw'][$i]),
                'rw' => trim($matches['rw'][$i]),
                'eng' => trim($matches['eng'][$i]),
                'bra' => trim($matches['bra'][$i]),
                'gear' => trim($matches['gear'][$i]),
                'susp' => trim($matches['susp'][$i]),
                'tyres' => trim($matches['tyres'][$i]),
                'comments' => $comments,
            ];
        }
    }

    return $practiceLaps;
}

function toJSON($postraceHtml)
{
    $data = [
        'practice' => getPracticeLaps($postraceHtml),
    ];

    return json_encode($data);
}

$postraceHtml = file_get_contents('seasons/89/S89R1 Slovakiaring.html');
echo "<xmp>";
print_r(toJSON($postraceHtml));
