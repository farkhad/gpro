<?php

/**
 *
 * Parse Sponsor information page into Array of Data
 */

namespace Gpro;

class SponsorParser extends PageParser
{
    public array $attributes = [];
    public string $feedback = '';

    public function parse()
    {
        $pattern = '|<div class="column left halves nomargin height500".+?'
            . '<tr data-step="2".+?>.+?<div class="flag" alt="(?<finances>[0-9]+?)".+?'
            . '<tr data-step="3".+?>.+?<div class="flag" alt="(?<expectations>[0-9]+?)".+?'
            . '<tr data-step="4".+?>.+?<div class="flag" alt="(?<patience>[0-9]+?)".+?'
            . '<tr data-step="5".+?>.+?<div class="flag" alt="(?<reputation>[0-9]+?)".+?'
            . '<tr data-step="6".+?>.+?<div class="flag" alt="(?<image>[0-9]+?)".+?'
            . '<tr data-step="7".+?>.+?<div class="flag" alt="(?<negotiation>[0-9]+?)".+?'
            . '<div class="column halves height500" data-step="9".+?'
            . '<table cellspacing="0" cellpadding="0" class="center">'
            . '(?<feedback>.*?)'
            . '<td class="center" colspan="2">'
            . '|is';
        if (!preg_match($pattern, $this->subject, $matches)) {
            return false;
        }

        $this->attributes = [
            'finances' => (int) trim($matches['finances']),
            'expectations' => (int) trim($matches['expectations']),
            'patience' => (int) trim($matches['patience']),
            'reputation' => (int) trim($matches['reputation']),
            'image' => (int) trim($matches['image']),
            'negotiation' => (int) trim($matches['negotiation']),
        ];

        $feedback = trim($matches['feedback']);

        $matches = [];
        if (preg_match('|<font color="yellow">(?<question>.+?)</font>|is', $feedback, $matches)) {
            $this->feedback = trim(strip_tags($matches['question']));
        } else if (preg_match('|<td colspan="2">(?<attention>.+?)</td>|is', $feedback, $matches)) {
            $this->feedback = trim(strip_tags($matches['attention']));
        }
    }

    public function toArray()
    {
        return [
            'attributes' => $this->attributes,
            'feedback' => $this->feedback,
        ];
    }
}
