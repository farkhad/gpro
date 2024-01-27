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
    public ?int $start = null;
    public ?int $end = null;

    public function parse()
    {
        $pattern = '|<tr onmouseover.+?>.+?'
            . '<a href="TrackDetails\.asp\?id=(?<trackId>[0-9]+?)">.+?'
            . '<td align="center".*?>(?<date>.+?)</td>.+?'
            . '<td align="center">(?<winner>.+?)</td>.+?'
            . '|is'
        ;
        if (!preg_match_all($pattern, $this->subject, $matches)) {
            return false;
        }
        $winnerPattern = '|<a href="ManagerProfile\.asp\?IDM=(?<winnerId>[0-9]+?)">|is';

        $tz = new DateTimeZone(\GPRO_TIMEZONE);
        foreach ($matches['trackId'] as $i => $track) {
            $date = trim($matches['date'][$i]);
            if (preg_match('/(?<relativeDate>Today|Yesterday)/is', $date, $m)) {
                $date = $m['relativeDate'];
            }

            $dt = new DateTime($date, $tz);

            if ($i === 0) {
                $this->start = $dt->getTimestamp();
            } elseif ($i === 16) {
                $this->end = $dt->getTimestamp();
            }

            $winnerId = null;
            if (preg_match($winnerPattern, trim($matches['winner'][$i]), $m)) {
                $winnerId = (int) $m['winnerId'];
            }


            $this->calendar[] = [
                'track_id' => (int) $matches['trackId'][$i],
                'date' => $dt->getTimestamp(),
                'winner_name' => trim(strip_tags($matches['winner'][$i])),
                'winner_id' => $winnerId,
            ];
        }

        $pattern = '|<td align="center">T\.</td>.+?'
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
            'start' => $this->start,
            'end' => $this->end,
            'test_track_id' => $this->testTrackId,
        ];
    }
}
