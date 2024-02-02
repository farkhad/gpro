<?php

/**
 *
 * Parse Track Details page into Array of Data
 */

namespace Gpro;

class TrackDetailsParser extends PageParser
{
    public array $history = [];

    public function parse()
    {
        $pattern = '|<TR onmouseover.+?>.+?'
            . '<TD.+?class="center">(?<season>[0-9]+?)</TD>.+?'
            . '<TD.+?class="center">(?<race>[0-9]+?)</TD>.+?'
            . '<TD.*?><a href="ManagerProfile\.asp\?IDM=.+?<br>(?<time>.+?)s</TD>.+?'
            . '|is'
        ;
        if (!preg_match_all($pattern, $this->subject, $matches)) {
            return false;
        }

        foreach ($matches['season'] as $i => $season) {
            $timeSlots = explode('h', $matches['time'][$i]);
            $absTime = $timeSlots[0]*60*60;

            $timeSlots = explode(':', $timeSlots[1]);
            $absTime += $timeSlots[0]*60;
            $absTime += (float) $timeSlots[1];

            $this->history[] = [
                'season' => (int) $matches['season'][$i],
                'race' => (int) $matches['race'][$i],
                'time' => $matches['time'][$i],
                'abs_time' => (float) $absTime,
            ];
        }
    }

    public function toArray()
    {
        return $this->history;
    }
}
