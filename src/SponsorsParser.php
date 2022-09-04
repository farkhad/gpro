<?php

/**
 *
 * Parse Sponsors page into Array of Data
 */

namespace Gpro;

// TODO add question flag, Communications from sponsor staff
class SponsorsParser extends PageParser
{

    public function parse()
    {
        $pattern = '|<form action="NegotiationsOverview.asp".+?'
            . '<table.+?>(?<contracts>.+?)</table>.+?'
            . '<TABLE id="ongnegsTable".+?>(?<negotiations>.+?)<tr class="static">'
            . '|is';
        if (!preg_match($pattern, $this->subject, $matches)) {
            return false;
        }
        $contracts = $matches['contracts'];
        $negotiations = $matches['negotiations'];

        $pattern = '|<td.+?>(?<name>.+?)</td>.+?'
            . '<td.+?>(?<spot>.+?)</td>.+?'
            . '<td.+?>(?<amount>.+?)</td>.+?'
            . '<td.+?>(?<status>.+?)</td>.+?'
            . '<td.+?>(?<races>.+?)</td>.+?'
            . '<td.+?>(?<satisfaction>.+?)</td>'
            . '|is';

        $matches = [];
        if (preg_match_all($pattern, $contracts, $matches)) {
            $this->contracts = [];
            foreach ($matches['spot'] as $i => $spot) {
                $id = '-';
                $name = trim($matches['name'][$i]);
                if (preg_match('/"NegotiateSponsor.asp\?ID=([0-9]+?)"/i', $name, $mName)) {
                    $id = (int) $mName[1];
                    $name = strip_tags($name);
                }

                $amount = trim($matches['amount'][$i]);
                if (preg_match('/\$([0-9\.]+)$/', $amount, $mAmount)) {
                    $amount = (int) str_replace('.', '', $mAmount[1]);
                }
                $races = trim($matches['races'][$i]);
                $races = $races === '-' ? '-' : (int) $races;

                $satisfaction = trim($matches['satisfaction'][$i]);
                $satisfaction = $satisfaction === '-'
                    ? '-'
                    : substr_count($matches['satisfaction'][$i], '<img');

                $this->contracts[] = [
                    'id' => $id,
                    'name' => $name,
                    'spot' => trim($spot),
                    'amount' => $amount,
                    'status' => trim(strip_tags($matches['status'][$i])),
                    'races' => $races,
                    'satisfaction' => $satisfaction,
                ];
            }
        }

        $pattern = '|<td.+?>(?<name>.+?)</td>.+?'
            . '<td.+?>(?<spot>.+?)</td>.+?'
            . '<td.+?>(?<amount>.+?)</td>.+?'
            . '<td.+?>(?<duration>.+?)</td>.+?'
            . '<td.+?>(?<progress>.+?)</td>.+?'
            . '<td.+?>.+?<span.+?>(?<priority>.+?)</span>.+?</td>.+?'
            . '<td.+?>(?<contested>.+?)</td>.+?'
            . '<td.+?>(?<avg_progress>.+?)</td>'
            . '|is';

        $matches = [];

        if (preg_match_all($pattern, $negotiations, $matches)) {
            $this->negotiations = [];
            foreach ($matches['name'] as $i => $name) {
                $id = '-';
                $name = trim(str_replace('&nbsp;', '', $name));
                if (preg_match('/"NegotiateSponsor.asp\?ID=([0-9]+?)"/i', $name, $mName)) {
                    $id = (int) $mName[1];
                    $name = strip_tags($name);
                }

                $amount = trim(strip_tags($matches['amount'][$i]));
                if (preg_match('/\$([0-9\.]+)$/', $amount, $mAmount)) {
                    $amount = (int) str_replace('.', '', $mAmount[1]);
                }

                $color = 'lime';
                if (preg_match('/color="(.+?)"/i', $matches['contested'][$i], $mContested)) {
                    $color = $mContested[1];
                }

                $this->negotiations[] = [
                    'id' => $id,
                    'name' => $name,
                    'spot' => trim(strip_tags($matches['spot'][$i])),
                    'duration' => trim(strip_tags($matches['duration'][$i])),
                    'progress' => (float) str_replace('%', '', trim(strip_tags($matches['progress'][$i]))),
                    'amount' => $amount,
                    'priority' => (int) trim($matches['priority'][$i]),
                    'contested' => trim(strip_tags($matches['contested'][$i])),
                    'contested_color' => $color,
                    'avg_progress' => (float) str_replace('%', '', trim(strip_tags($matches['avg_progress'][$i]))),
                ];
            }
        }
    }

    public function toArray()
    {
        return [
            'contracts' => $this->contracts,
            'negotiations' => $this->negotiations,
        ];
    }
}
