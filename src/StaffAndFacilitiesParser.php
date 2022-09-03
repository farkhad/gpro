<?php

/**
 *
 * Parse Staff and Facilities page into Array of Data
 */

namespace Gpro;

class StaffAndFacilitiesParser extends PageParser
{
    public int $overall;
    public int $salary;
    public int $maintenance;
    public array $staff = [];
    public array $facilities = [];
    public int $training;

    public function parse()
    {
        $pattern = '|<table class="squashed leftalign".+?<td width="100">(?<overall>[0-9]+?)</td>.+?'
            . '<th class="center">.+?\$(?<salary>[0-9\.]+?)\&.+?\$(?<maintenance>[^<]+?)</th>.+?'
            . '<td width="100">(?<exp>[0-9]+?)</td>.+?'
            . '<td>(?<mot>[0-9]+?)</td>.+?'
            . '<td>(?<tech>[0-9]+?)</td>.+?'
            . '<td>(?<stress>[0-9]+?)</td>.+?'
            . '<td>(?<con>[0-9]+?)</td>.+?'
            . '<td>(?<eff>[0-9]+?)</td>.+?'
            . '<td width="100">(?<wind>[0-9]+?)</td>.+?'
            . '<td>(?<pitstop>[0-9]+?)</td>.+?'
            . '<td>(?<rd_workshop>[0-9]+?)</td>.+?'
            . '<td>(?<rd_design>[0-9]+?)</td>.+?'
            . '<td>(?<eng_workshop>[0-9]+?)</td>.+?'
            . '<td>(?<alloy>[0-9]+?)</td>.+?'
            . '<td>(?<commercial>[0-9]+?)</td>.+?'
            . '<p.+?>.+?(?<training>[0-9]+?)</p>'
            . '|is';
        if (!preg_match($pattern, $this->subject, $matches)) {
            return false;
        }

        $this->overall = (int) trim($matches['overall']);
        $this->salary = (int) str_replace('.', '', trim($matches['salary']));
        $this->maintenance = (int) str_replace('.', '', trim($matches['maintenance']));
        $this->staff = [
            'exp' => (int) trim($matches['exp']),
            'mot' => (int) trim($matches['mot']),
            'tech' => (int) trim($matches['tech']),
            'stress' => (int) trim($matches['stress']),
            'con' => (int) trim($matches['con']),
            'eff' => (int) trim($matches['eff']),
        ];
        $this->facilities = [
            'wind' => (int) trim($matches['wind']),
            'pitstop' => (int) trim($matches['pitstop']),
            'rd_workshop' => (int) trim($matches['rd_workshop']),
            'rd_design' => (int) trim($matches['rd_design']),
            'eng_workshop' => (int) trim($matches['eng_workshop']),
            'alloy' => (int) trim($matches['alloy']),
            'commercial' => (int) trim($matches['commercial']),
        ];
        $this->training = (int) trim($matches['training']);
    }

    public function toArray()
    {
        return [
            'overall' => $this->overall,
            'salary' => $this->salary,
            'maintenance' => $this->maintenance,
            'staff' => $this->staff,
            'facilities' => $this->facilities,
            'training' => $this->training
        ];
    }
}
