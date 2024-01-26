<?php

/**
 *
 * Parse Driver profile page into Array of Data
 */

namespace Gpro;

class DriverProfileParser extends PageParser
{
    public array $startedWorking = [];

    public function parse()
    {
        $pattern = '|<td align=center>.+?href="ManagerProfile\.asp\?IDM=(?<managerId>[0-9]+?)">.+?</a></td>.+?'
            . '<td width="130" align="center">.+?Season (?<seasonStarted>[0-9]+?), Race (?<raceStarted>[0-9]+?)\b.+?</td>.+?'
            . '|is'
        ;
        if (!preg_match_all($pattern, $this->subject, $matches)) {
            return false;
        }

        $idx = count($matches['managerId'])-1;

        $this->startedWorking = [
            'managerId' => (int) $matches['managerId'][$idx],
            'season' => (int) $matches['seasonStarted'][$idx],
            'race' => (int) $matches['raceStarted'][$idx]
        ];
    }

    public function toArray()
    {
        return $this->startedWorking;
    }
}
