<?php

/**
 *
 * Parse All Tracks page into Array of Data
 */

namespace Gpro;

class TracksParser extends PageParser
{
    public array $tracks = [];

    public function parse()
    {
        $pattern = '|<tr onmouseover.+?>.+?'
            . '<td class="leftalign"><a.+?href=".+?\?id=(?<trackId>[0-9]+?)">(?<trackName>.+?)</a></td>.+?'
            . '<td align="center">(?<country>.+?)</td>.+?'
            . '<td align="center">(?<raceDistance>.+?)</td>.+?'
            . '<td align="center">(?<laps>.+?)</td>.+?'
            . '<td align="center">(?<lapDistance>.+?)</td>.+?'
            . '<td class="leftalign".+?title="(?<power>.+?)">.+?</td>.+?'
            . '<td class="leftalign".+?title="(?<handling>.+?)">.+?</td>.+?'
            . '<td class="leftalign".+?title="(?<acceleration>.+?)">.+?</td>.+?'
            . '<td align="center">(?<category>.+?)</td>.+?'
            . '<td align="center">(?<gpHeld>.+?)</td>.+?'
            . '|is'
        ;
        if (!preg_match_all($pattern, $this->subject, $matches)) {
            return false;
        }

        foreach ($matches['trackId'] as $i => $track) {
            $this->tracks[] = [
                'id' => (int) $matches['trackId'][$i],
                'name' => $matches['trackName'][$i],
                'country' => $matches['country'][$i],
                'race_distance' => (float) $matches['raceDistance'][$i],
                'laps' => (int) $matches['laps'][$i],
                'lap_distance' => (float) (str_replace('&nbsp;km', '', $matches['lapDistance'][$i])),
                'power' => (int) $matches['power'][$i],
                'handling' => (int) $matches['handling'][$i],
                'acceleration' => (int) $matches['acceleration'][$i],
                'category' => $matches['category'][$i],
                'gp_held' => (int) $matches['gpHeld'][$i],
            ];
        }
    }

    public function toArray()
    {
        return $this->tracks;
    }
}
