<?php

/**
 *
 * Parse Car Character Points from Testing page into Array of Data
 */

namespace Gpro;

class CCPParser extends PageParser
{
    public array $points = [
        'testing' => [],
        'rd' => [],
        'eng' => [],
        'ccp' => [],
    ];

    public function parse()
    {
        $pattern = '|<table.+?class="styled leftalign borderbottom".+?'
            . '<td class="center">(?<testP>.+?)</td>.+?'
            . '<td class="center">(?<testH>.+?)</td>.+?'
            . '<td class="center">(?<testA>.+?)</td>.+?'
            . '<td class="center">(?<rdP>.+?)</td>.+?'
            . '<td class="center">(?<rdH>.+?)</td>.+?'
            . '<td class="center">(?<rdA>.+?)</td>.+?'
            . '<td class="center">(?<engP>.+?)</td>.+?'
            . '<td class="center">(?<engH>.+?)</td>.+?'
            . '<td class="center">(?<engA>.+?)</td>.+?'
            . '<td class="center">(?<ccpP>.+?)</td>.+?'
            . '<td class="center">(?<ccpH>.+?)</td>.+?'
            . '<td class="center">(?<ccpA>.+?)</td>.+?'
            . '|is';
        if (!preg_match($pattern, $this->subject, $matches)) {
            return false;
        }
        $this->points['testing'] = [
            (float) trim($matches['testP']),
            (float) trim($matches['testH']),
            (float) trim($matches['testA']),
        ];
        $this->points['rd'] = [
            (float) trim($matches['rdP']),
            (float) trim($matches['rdH']),
            (float) trim($matches['rdA']),
        ];
        $this->points['eng'] = [
            (float) trim($matches['engP']),
            (float) trim($matches['engH']),
            (float) trim($matches['engA']),
        ];
        $this->points['ccp'] = [
            (float) trim($matches['ccpP']),
            (float) trim($matches['ccpH']),
            (float) trim($matches['ccpA']),
        ];
    }

    public function toArray()
    {
        return $this->points;
    }
}
