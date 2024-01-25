<?php

/**
 *
 * Parse Season Calendar page into Array of Data
 */

namespace Gpro;

use DateTime;
use DateTimeZone;

class SeasonCalendarParser extends PageParser
{
    public array $calendar = [];
    public ?int $testTrackId = null;

    public function parse()
    {
        $pattern = '|<tr onmouseover.+?>.+?'
            . '<a href="TrackDetails\.asp\?id=(?<trackId>[0-9]+?)">.+?'
            . '<td align="center".*?>(?<date>.+?)</td>'
            . '|is'
        ;
        if (!preg_match_all($pattern, $this->subject, $matches)) {
            return false;
        }

        $tz = new DateTimeZone(\GPRO_TIMEZONE);
        foreach ($matches['trackId'] as $i => $track) {
            $dt = new DateTime(trim($matches['date'][$i]), $tz);

            $this->calendar[] = [
                'track_id' => (int) $matches['trackId'][$i],
                'date' => $dt->getTimestamp(),
            ];
        }

        $pattern = '|<td align="center">T.</td>.+?'
            . '<a href="TrackDetails\.asp\?id=(?<trackId>[0-9]+?)">.+?'
            . '|is'
        ;
        $matches = [];
        if (preg_match($pattern, $this->subject, $matches)) {
            $this->testTrackId = (int) $matches['trackId'];
        }
    }

    public function toArray()
    {
        return [
            'tracks' => $this->calendar,
            'test' => $this->testTrackId
        ];
    }
}
