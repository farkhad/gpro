<?php

/**
 *
 * Parse Home page into Array of Data
 */

namespace Gpro;

class HomeParser extends PageParser
{
    public ?int $season = null;

    public function parse()
    {
        $pattern = '|<div id="racebar">.+?'
		    . '<h1>.+?Season (?<season>[0-9]+?),.+?'
            . '|is'
        ;
        if (!preg_match($pattern, $this->subject, $matches)) {
            return false;
        }

        $this->season = (int) $matches['season'];
    }

    public function toArray()
    {
        return ['season' => $this->season];
    }
}
