<?php

/**
 *
 * Parse Race Analysis page into Array of Data
 */

namespace Gpro;

class RaceAnalysisParser extends PageParser
{
    public array $practiceLaps = [];
    public array $car = [];
    public array $lapInfo = [];
    public array $driver = [];
    public array $q1 = [];
    public array $q2 = [];
    public array $race = [];
    public array $weather = [];
    public array $tyreSupplier = [];
    public array $finances = [];
    public array $sf = [];
    public array $sponsors = [];
    public array $carPoints = [];

    public function parse()
    {
        $this->parsePracticeLaps();
        $this->parseCarAndLapInfo();
        $this->parseRaceInfo();
    }

    public function toArray()
    {
        $this->car['points'] = $this->carPoints;

        return [
            'practice' => $this->practiceLaps,
            'car' => $this->car,
            'q1' => $this->q1,
            'q2' => $this->q2,
            'race' => $this->race,
            'weather' => $this->weather,
            'driver' => $this->driver,
            'tyre_supplier' => $this->tyreSupplier,
            'finances' => $this->finances,
            'sf' => $this->sf,
            'sponsors' => $this->sponsors,
        ];
    }

    public function parsePracticeLaps()
    {
        $pattern = '|<div id="PracticeData".+?<table class="styled borderbottom flag".+?>.+?<table.+?class="styled borderbottom".+?>(.+?)</table>|is';

        if (!preg_match($pattern, $this->subject, $matches)) {
            return false;
        }

        $html = $matches[1];
        $matches = [];

        $pattern = '|<tr class="pointerhand".+?'
            . '<td.+?>(?<lap>.+?)</td>.+?'
            . '<td.+?>(?<gross>.+?)</td>.+?'
            . '<td.+?>(?<dm>.+?)</td>.+?'
            . '<td.+?>(?<net>.+?)</td>.+?'
            . '<td.+?>(?<fw>.+?)</td>.+?'
            . '<td.+?>(?<rw>.+?)</td>.+?'
            . '<td.+?>(?<eng>.+?)</td>.+?'
            . '<td.+?>(?<bra>.+?)</td>.+?'
            . '<td.+?>(?<gear>.+?)</td>.+?'
            . '<td.+?>(?<susp>.+?)</td>.+?'
            . '<td.+?>(?<tyres>.+?)</td>.+?'
            . '<td.+?>.+?\.innerHTML\=\'<br>(?<comm>.+?)\';.+?</td>.+?'
            . '</tr>|is';
        if (preg_match_all($pattern, $html, $matches)) {
            foreach ($matches['lap'] as $i => $lap) {
                $comments = explode('<br>', trim($matches['comm'][$i]));
                array_walk($comments, fn (&$element) => $element = strip_tags($element));

                $this->practiceLaps[$i] = [
                    'gross' => trim(strip_tags($matches['gross'][$i])),
                    'net' => trim(strip_tags($matches['net'][$i])),
                    'dm' => trim(strip_tags($matches['dm'][$i])),
                    'setup' => [
                        'fw' => (int) trim($matches['fw'][$i]),
                        'rw' => (int) trim($matches['rw'][$i]),
                        'eng' => (int) trim($matches['eng'][$i]),
                        'bra' => (int) trim($matches['bra'][$i]),
                        'gear' => (int) trim($matches['gear'][$i]),
                        'susp' => (int) trim($matches['susp'][$i]),
                    ],
                    'tyres' => trim($matches['tyres'][$i]),
                    'comments' => $comments,
                ];
            }
        }
    }

    public function parseCarAndLapInfo()
    {
        $pattern = '|<div class="column right fiftyfive".+?'
            . '<div class="inner">.+?'
            . '<table class="styled bordered center".+?'
            . '<tr onmouseover.+?>.*?'
            . '<td.+?>(?<cha>.+?)</td>.*?'
            . '<td.+?>(?<eng>.+?)</td>.*?'
            . '<td.+?>(?<fw>.+?)</td>.*?'
            . '<td.+?>(?<rw>.+?)</td>.*?'
            . '<td.+?>(?<underb>.+?)</td>.*?'
            . '<td.+?>(?<sidep>.+?)</td>.*?'
            . '<td.+?>(?<cool>.+?)</td>.*?'
            . '<td.+?>(?<gear>.+?)</td>.*?'
            . '<td.+?>(?<bra>.+?)</td>.*?'
            . '<td.+?>(?<susp>.+?)</td>.*?'
            . '<td.+?>(?<elec>.+?)</td>.*?'
            . '</tr>.+?'
            . '<tr onmouseover.+?>.*?'
            . '<td.+?>(?<chaStart>.+?)%</td>.*?'
            . '<td.+?>(?<engStart>.+?)%</td>.*?'
            . '<td.+?>(?<fwStart>.+?)%</td>.*?'
            . '<td.+?>(?<rwStart>.+?)%</td>.*?'
            . '<td.+?>(?<underbStart>.+?)%</td>.*?'
            . '<td.+?>(?<sidepStart>.+?)%</td>.*?'
            . '<td.+?>(?<coolStart>.+?)%</td>.*?'
            . '<td.+?>(?<gearStart>.+?)%</td>.*?'
            . '<td.+?>(?<braStart>.+?)%</td>.*?'
            . '<td.+?>(?<suspStart>.+?)%</td>.*?'
            . '<td.+?>(?<elecStart>.+?)%</td>.*?'
            . '</tr>.+?'
            . '<tr onmouseover.+?>.*?'
            . '<td.+?>(?<chaFinish>.+?)%</td>.*?'
            . '<td.+?>(?<engFinish>.+?)%</td>.*?'
            . '<td.+?>(?<fwFinish>.+?)%</td>.*?'
            . '<td.+?>(?<rwFinish>.+?)%</td>.*?'
            . '<td.+?>(?<underbFinish>.+?)%</td>.*?'
            . '<td.+?>(?<sidepFinish>.+?)%</td>.*?'
            . '<td.+?>(?<coolFinish>.+?)%</td>.*?'
            . '<td.+?>(?<gearFinish>.+?)%</td>.*?'
            . '<td.+?>(?<braFinish>.+?)%</td>.*?'
            . '<td.+?>(?<suspFinish>.+?)%</td>.*?'
            . '<td.+?>(?<elecFinish>.+?)%</td>.*?'
            . '</tr>.+?'
            . '<table.+?>.*?(?<lapInfo>.+?)</table>|is';

        if (!preg_match($pattern, $this->subject, $matches)) {
            return false;
        }

        $this->car = [
            'cha' => [
                'level' => (int) $matches['cha'],
                'start' => (int) $matches['chaStart'],
                'finish' => (int) $matches['chaFinish'],
            ],
            'eng' => [
                'level' => (int) $matches['eng'],
                'start' => (int) $matches['engStart'],
                'finish' => (int) $matches['engFinish'],
            ],
            'fw' => [
                'level' => (int) $matches['fw'],
                'start' => (int) $matches['fwStart'],
                'finish' => (int) $matches['fwFinish'],
            ],
            'rw' => [
                'level' => (int) $matches['rw'],
                'start' => (int) $matches['rwStart'],
                'finish' => (int) $matches['rwFinish'],
            ],
            'underb' => [
                'level' => (int) $matches['underb'],
                'start' => (int) $matches['underbStart'],
                'finish' => (int) $matches['underbFinish'],
            ],
            'sidep' => [
                'level' => (int) $matches['sidep'],
                'start' => (int) $matches['sidepStart'],
                'finish' => (int) $matches['sidepFinish'],
            ],
            'cool' => [
                'level' => (int) $matches['cool'],
                'start' => (int) $matches['coolStart'],
                'finish' => (int) $matches['coolFinish'],
            ],
            'gear' => [
                'level' => (int) $matches['gear'],
                'start' => (int) $matches['gearStart'],
                'finish' => (int) $matches['gearFinish'],
            ],
            'bra' => [
                'level' => (int) $matches['bra'],
                'start' => (int) $matches['braStart'],
                'finish' => (int) $matches['braFinish'],
            ],
            'susp' => [
                'level' => (int) $matches['susp'],
                'start' => (int) $matches['suspStart'],
                'finish' => (int) $matches['suspFinish'],
            ],
            'elec' => [
                'level' => (int) $matches['elec'],
                'start' => (int) $matches['elecStart'],
                'finish' => (int) $matches['elecFinish'],
            ],
        ];

        $pattern = '|<tr onmouseover.+?>.*?'
            . '<td.+?>(?<lap>.+?)</td>.*?'
            . '<td.+?>(?<lapTime>.+?)</td>.*?'
            . '<td.+?>(?<pos>.+?)</td>.*?'
            . '<td.+?>(?<tyres>.+?)</td>.*?'
            . '<td.+?>(?<weather>.+?)</td>.*?'
            . '<td.+?>(?<temp>.+?)</td>.*?'
            . '<td.+?>(?<hum>.+?)</td>.*?'
            . '<td.+?>(?<events>.+?)</td>.*?'
            . '</tr>.+?|is';
        $lapInfo = $matches['lapInfo'];
        $matches = [];

        if (preg_match_all($pattern, $lapInfo, $matches)) {
            foreach ($matches['lap'] as $i => $lap) {
                $hum = trim(strip_tags($matches['hum'][$i]));
                $temp = trim(strip_tags($matches['temp'][$i]));

                $this->lapInfo[$i] = [
                    'time' => trim(strip_tags($matches['lapTime'][$i])),
                    'pos' => (int) trim(strip_tags($matches['pos'][$i])),
                    // 'tyres' => trim(strip_tags($matches['tyres'][$i])),
                    // 'weather' => trim(strip_tags($matches['weather'][$i])),
                    'temp' => (int) str_replace(['&#176;', '°'], ['', ''], $temp),
                    'hum' => (int) str_replace('%', '', $hum),
                    'events' => trim(strip_tags($matches['events'][$i])),
                ];
            }
        }
    }

    public function parseRaceInfo()
    {
        $pattern = '|<div class="column left fortyfive nomargin".+?'
            . '<table.+?class="styled bordered center".+?'
            . '<td align=.+?>(?<q1time>.+?) \(.+?#(?<q1pos>[0-9]+?)</a>\)</td>.*?'
            . '<td align=.+?>(?<q2time>.+?) \(.+?#(?<q2pos>[0-9]+?)</a>\)</td>.*?'
            . '<tr onmouseover.+?>.*?'
            . '<td align=.+?>.+?'
            . '<td align=.+?>(?<q1fw>.+?)</td>.*?'
            . '<td align=.+?>(?<q1rw>.+?)</td>.*?'
            . '<td align=.+?>(?<q1eng>.+?)</td>.*?'
            . '<td align=.+?>(?<q1bra>.+?)</td>.*?'
            . '<td align=.+?>(?<q1gear>.+?)</td>.*?'
            . '<td align=.+?>(?<q1susp>.+?)</td>.*?'
            . '<td align=.+?>(?<q1tyres>.+?)</td>.*?'
            . '</tr>.*?'
            . '<tr onmouseover.+?>.*?'
            . '<td align=.+?>.+?'
            . '<td align=.+?>(?<q2fw>.+?)</td>.*?'
            . '<td align=.+?>(?<q2rw>.+?)</td>.*?'
            . '<td align=.+?>(?<q2eng>.+?)</td>.*?'
            . '<td align=.+?>(?<q2bra>.+?)</td>.*?'
            . '<td align=.+?>(?<q2gear>.+?)</td>.*?'
            . '<td align=.+?>(?<q2susp>.+?)</td>.*?'
            . '<td align=.+?>(?<q2tyres>.+?)</td>.*?'
            . '</tr>.*?'
            . '<tr onmouseover.+?>.*?'
            . '<td align=.+?>.+?'
            . '<td align=.+?>(?<raceFw>.+?)</td>.*?'
            . '<td align=.+?>(?<raceRw>.+?)</td>.*?'
            . '<td align=.+?>(?<raceEng>.+?)</td>.*?'
            . '<td align=.+?>(?<raceBra>.+?)</td>.*?'
            . '<td align=.+?>(?<raceGear>.+?)</td>.*?'
            . '<td align=.+?>(?<raceSusp>.+?)</td>.*?'
            . '<td align=.+?>(?<raceTyres>.+?)</td>.*?'
            . '</tr>.*?'
            . '<td style=.+?>(?<q1risk>.+?)</td>.*?'
            . '<td style=.+?>(?<q2risk>.+?)</td>.*?'
            . '<td colspan="5">(?<startingRisk>.+?)</td>.*?'
            . '<tr>.*?'
            . '<td.*?>(?<OT>.+?)</td>.*?'
            . '<td.*?>(?<DF>.+?)</td>.*?'
            . '<td.*?>(?<CTDry>.+?)</td>.*?'
            . '<td.*?>(?<CTWet>.+?)</td>.*?'
            . '<td.*?>(?<MF>.+?)</td>.*?'
            . 'href="DriverProfile.asp\?ID=(?<driverId>[0-9]+?)">(?<driverName>.+?)</a>.*?'
            . '<td.*?>(?<OA>.+?)</td>.*?'
            . '<td.*?>(?<CON>.+?)</td>.*?'
            . '<td.*?>(?<TAL>.+?)</td>.*?'
            . '<td.*?>(?<AGG>.+?)</td>.*?'
            . '<td.*?>(?<EXP>.+?)</td>.*?'
            . '<td.*?>(?<TEI>.+?)</td>.*?'
            . '<td.*?>(?<STA>.+?)</td>.*?'
            . '<td.*?>(?<CHA>.+?)</td>.*?'
            . '<td.*?>(?<MOT>.+?)</td>.*?'
            . '<td.*?>(?<REP>.+?)</td>.*?'
            . '<td.*?>(?<WEI>.+?)</td>.*?'
            . '</tr>.*?'
            . '<td.*?>\((?<OADiff>.+?)\)</td>.*?'
            . '<td.*?>\((?<CONDiff>.+?)\)</td>.*?'
            . '<td.*?>\((?<TALDiff>.+?)\)</td>.*?'
            . '<td.*?>\((?<AGGDiff>.+?)\)</td>.*?'
            . '<td.*?>\((?<EXPDiff>.+?)\)</td>.*?'
            . '<td.*?>\((?<TEIDiff>.+?)\)</td>.*?'
            . '<td.*?>\((?<STADiff>.+?)\)</td>.*?'
            . '<td.*?>\((?<CHADiff>.+?)\)</td>.*?'
            . '<td.*?>\((?<MOTDiff>.+?)\)</td>.*?'
            . '<td.*?>\((?<REPDiff>.+?)\)</td>.*?'
            . '<td.*?>\((?<WEIDiff>.+?)\)</td>.*?'
            . '<table class="styled center".+?'
            . '<td title=.+?>(?<energyBeforeQ1>.+?)</td>.*?'
            . '<td title=.+?>(?<energyAfterQ1>.+?)</td>.*?'
            . '<td title=.+?>(?<energyBeforeQ2>.+?)</td>.*?'
            . '<td title=.+?>(?<energyAfterQ2>.+?)</td>.*?'
            . '<td title=.+?>(?<energyBeforeRace>.+?)</td>.*?'
            . '<td title=.+?>(?<energyAfterRace>.+?)</td>.*?'
            . '<table class="styled bordered center">.*?'
            . '<td>(?<startPos>.+?)</td>.*?'
            . '<td>(?<finishPos>.+?)</td>.*?'
            . '<td align=.+?>(?<P>.+?)</td>.*?'
            . '<td align=.+?>(?<H>.+?)</td>.*?'
            . '<td align=.+?>(?<A>.+?)</td>.*?'
            . '<td colspan="2">(?<tyreSupplier>.+?)</td>.*?'
            . '<td>(?<tyrePeakTemp>.+?)</td>.*?'
            . '<td width="100%".*?>(?<tyreDryPerf>.+?)</td>.*?'
            . '<td width="100%".*?>(?<tyreWetPerf>.+?)</td>.*?'
            . '<td width="100%".*?>(?<tyreDurab>.+?)</td>.*?'
            . '<td width="100%".*?>(?<tyreWarmup>.+?)</td>.*?'
            . '<td align="center">.*?(?<weatherQ1Temp>[0-9]+?)&#176;.*?(?<weatherQ1Hum>[0-9]+?)%.*?</td>.*?'
            . '<td align="center">.*?(?<weatherQ2Temp>[0-9]+?)&#176;.*?(?<weatherQ2Hum>[0-9]+?)%.*?</td>.*?'
            . '<td>.*?(?<weatherR1Temp1>[0-9]+?)&deg;.+?(?<weatherR1Temp2>[0-9]+?)&deg;.+?'
            . '(?<weatherR1Hum1>[0-9]+?)%.+?(?<weatherR1Hum2>[0-9]+?)%.+?'
            . '(?<weatherR1Rain>[0-9]+?)%.*?</td>.*?'
            . '<td>.*?(?<weatherR2Temp1>[0-9]+?)&deg;.+?(?<weatherR2Temp2>[0-9]+?)&deg;.+?'
            . '(?<weatherR2Hum1>[0-9]+?)%.+?(?<weatherR2Hum2>[0-9]+?)%.+?'
            . '(?<weatherR2Rain>[0-9]+?)%.*?</td>.*?'
            . '<td>.*?(?<weatherR3Temp1>[0-9]+?)&deg;.+?(?<weatherR3Temp2>[0-9]+?)&deg;.+?'
            . '(?<weatherR3Hum1>[0-9]+?)%.+?(?<weatherR3Hum2>[0-9]+?)%.+?'
            . '(?<weatherR3Rain>[0-9]+?)%.*?</td>.*?'
            . '<td>.*?(?<weatherR4Temp1>[0-9]+?)&deg;.+?(?<weatherR4Temp2>[0-9]+?)&deg;.+?'
            . '(?<weatherR4Hum1>[0-9]+?)%.+?(?<weatherR4Hum2>[0-9]+?)%.+?'
            . '(?<weatherR4Rain>[0-9]+?)%.*?</td>.*?'
            . '<div data-step="9".+?(?<startFuel>[0-9]+?)&nbsp;.*?'
            . '<table.+?>(?<pitStops>.+?)</table>'
            . '(?<blockAfterPitStops>.+?)</div>.*?'
            . '<td class="center">(?<otInitBlocked>.+?)</td>.*?'
            . '<td class="center">(?<otInitSuccess>.+?)</td>.*?'
            . '<td class="center">(?<otUponYouBlocked>.+?)</td>.*?'
            . '<td class="center">(?<otUponYouSuccess>.+?)</td>.*?'
            . '<div id="dvFinAnalisysTable">.+?'
            . '<tr><td colspan="2".+?<tr>(?<earnings>.+?)</tr>'
            . '<tr><td colspan="2".+?<tr>(?<costs>.+?)<th colspan="2">.*?'
            . '<td>\$(?<total>.+?)</td>.*?'
            . '<td class="speccell">.+?>\$(?<balance>.+?)<.+?</td>'
            . '|is';

        if (!preg_match($pattern, $this->subject, $matches)) {
            return false;
        }

        $this->finances = [
            'total' => (int) str_replace('.', '', $matches['total']),
            'balance' => (int) str_replace('.', '', $matches['balance']),
        ];

        $earnings = $matches['earnings'];
        $costs = $matches['costs'];
        $pattern = '|<td>(?<moneyReason>.+?)\:.*?</td><td>.*?\$(?<moneyAmount>.+?)</|is';
        $mFinances = [];
        if (preg_match_all($pattern, $earnings, $mFinances)) {
            $this->finances['earnings'] = [];
            foreach ($mFinances['moneyReason'] as $i => $reason) {
                $this->finances['earnings'][$i] = [
                    'reason' => $reason,
                    'amount' => (int) str_replace('.', '', $mFinances['moneyAmount'][$i]),
                ];
            }
        }

        $mFinances = [];
        if (preg_match_all($pattern, $costs, $mFinances)) {
            $this->finances['costs'] = [];
            foreach ($mFinances['moneyReason'] as $i => $reason) {
                $this->finances['costs'][$i] = [
                    'reason' => $reason,
                    'amount' => (int) str_replace('.', '', $mFinances['moneyAmount'][$i]),
                ];
            }
        }

        $this->q1 = [
            'time' => trim($matches['q1time']),
            'pos' => (int) trim($matches['q1pos']),
            'setup' => [
                'fw' => (int) $matches['q1fw'],
                'rw' => (int) $matches['q1rw'],
                'eng' => (int) $matches['q1eng'],
                'bra' => (int) $matches['q1bra'],
                'gear' => (int) $matches['q1gear'],
                'susp' => (int) $matches['q1susp'],
            ],
            'tyres' => trim($matches['q1tyres']),
            'risk' => trim($matches['q1risk']),
        ];

        $this->q2 = [
            'time' => trim($matches['q2time']),
            'pos' => (int) trim($matches['q2pos']),
            'setup' => [
                'fw' => (int) $matches['q2fw'],
                'rw' => (int) $matches['q2rw'],
                'eng' => (int) $matches['q2eng'],
                'bra' => (int) $matches['q2bra'],
                'gear' => (int) $matches['q2gear'],
                'susp' => (int) $matches['q2susp'],
            ],
            'tyres' => trim($matches['q2tyres']),
            'risk' => trim($matches['q2risk']),
        ];

        $this->race = [
            'start' => (int) trim($matches['startPos']),
            'finish' => (int) trim($matches['finishPos']),
            'sr' => trim($matches['startingRisk']),
            'ot' => (int) trim($matches['OT']),
            'df' => (int) trim($matches['DF']),
            'mf' => (int) trim($matches['MF']),
            'ct_dry' => (int) trim($matches['CTDry']),
            'ct_wet' => (int) trim($matches['CTWet']),
            'ot_init_blocked' => (int) trim(strip_tags($matches['otInitBlocked'])),
            'ot_init_success' => (int) trim(strip_tags($matches['otInitSuccess'])),
            'ot_uponyou_blocked' => (int) trim(strip_tags($matches['otUponYouBlocked'])),
            'ot_uponyou_success' => (int) trim(strip_tags($matches['otUponYouSuccess'])),
            'setup' => [
                'fw' => (int) $matches['raceFw'],
                'rw' => (int) $matches['raceRw'],
                'eng' => (int) $matches['raceEng'],
                'bra' => (int) $matches['raceBra'],
                'gear' => (int) $matches['raceGear'],
                'susp' => (int) $matches['raceSusp'],
            ],
            'laps' => $this->lapInfo,
        ];

        $this->driver = [
            'OA' => (int) $matches['OA'],
            'CON' => (int) $matches['CON'],
            'TAL' => (int) $matches['TAL'],
            'AGG' => (int) $matches['AGG'],
            'EXP' => (int) $matches['EXP'],
            'TEI' => (int) $matches['TEI'],
            'STA' => (int) $matches['STA'],
            'CHA' => (int) $matches['CHA'],
            'MOT' => (int) $matches['MOT'],
            'REP' => (int) $matches['REP'],
            'WEI' => (int) $matches['WEI'],
            'diff' => [
                'OA' => (int) $matches['OADiff'],
                'CON' => (int) $matches['CONDiff'],
                // 'TAL' => (int) $matches['TALDiff'],
                'AGG' => (int) $matches['AGGDiff'],
                'EXP' => (int) $matches['EXPDiff'],
                'TEI' => (int) $matches['TEIDiff'],
                'STA' => (int) $matches['STADiff'],
                'CHA' => (int) $matches['CHADiff'],
                'MOT' => (int) $matches['MOTDiff'],
                'REP' => (int) $matches['REPDiff'],
                'WEI' => (int) $matches['WEIDiff'],
            ],
            'energy' => [
                'before_q1' => (int) str_replace('%', '', strip_tags($matches['energyBeforeQ1'])),
                'after_q1' => (int) str_replace('%', '', strip_tags($matches['energyAfterQ1'])),
                'before_q2' => (int) str_replace('%', '', strip_tags($matches['energyBeforeQ2'])),
                'after_q2' => (int) str_replace('%', '', strip_tags($matches['energyAfterQ2'])),
                'before_race' => (int) str_replace('%', '', strip_tags($matches['energyBeforeRace'])),
                'after_race' => (int) str_replace('%', '', strip_tags($matches['energyAfterRace'])),
            ],
        ];
        $this->car['P'] = (int) $matches['P'];
        $this->car['H'] = (int) $matches['H'];
        $this->car['A'] = (int) $matches['A'];

        $this->tyreSupplier = [
            'name' => str_replace('&nbsp;', '', trim(strip_tags($matches['tyreSupplier']))),
            'peak' => (int) str_replace('&deg;', '', trim(strip_tags($matches['tyrePeakTemp']))),
            'dry' => substr_count($matches['tyreDryPerf'], '<img'),
            'wet' => substr_count($matches['tyreWetPerf'], '<img'),
            'durability' => substr_count($matches['tyreDurab'], '<img'),
            'warmup' => substr_count($matches['tyreWarmup'], '<img'),
        ];

        $this->weather = [
            'quali' => [
                ['t' => (int) $matches['weatherQ1Temp'], 'h' => (int) $matches['weatherQ1Hum']],
                ['t' => (int) $matches['weatherQ2Temp'], 'h' => (int) $matches['weatherQ2Hum']],
            ],
            'race' => [
                [
                    't_low' => (int) $matches['weatherR1Temp1'],
                    't_high' => (int) $matches['weatherR1Temp2'],
                    'h_low' => (int) $matches['weatherR1Hum1'],
                    'h_high' => (int) $matches['weatherR1Hum2'],
                    'rain' => (int) $matches['weatherR1Rain'],
                ],
                [
                    't_low' => (int) $matches['weatherR2Temp1'],
                    't_high' => (int) $matches['weatherR2Temp2'],
                    'h_low' => (int) $matches['weatherR2Hum1'],
                    'h_high' => (int) $matches['weatherR2Hum2'],
                    'rain' => (int) $matches['weatherR2Rain'],
                ],
                [
                    't_low' => (int) $matches['weatherR3Temp1'],
                    't_high' => (int) $matches['weatherR3Temp2'],
                    'h_low' => (int) $matches['weatherR3Hum1'],
                    'h_high' => (int) $matches['weatherR3Hum2'],
                    'rain' => (int) $matches['weatherR3Rain'],
                ],
                [
                    't_low' => (int) $matches['weatherR4Temp1'],
                    't_high' => (int) $matches['weatherR4Temp2'],
                    'h_low' => (int) $matches['weatherR4Hum1'],
                    'h_high' => (int) $matches['weatherR4Hum2'],
                    'rain' => (int) $matches['weatherR4Rain'],
                ],
            ],
        ];

        $pitStops = $matches['pitStops'];
        $mPitStops = [];
        $pattern = '|<tr>.*?'
        . '<td class=.+?>.*?\(.+?&nbsp;(?<pitLap>[0-9]+?)\).*?</td>.*?'
        . '<td class=.+?>(?<pitReason>.+?)</td>.*?'
        . '<td class=.+?>.+?>(?<pitTyres>[0-9]+?)%<.+?</td>.*?'
        . '<td class=.+?>.+?>(?<pitFuel>[0-9]+?)%<.+?</td>.*?'
        . '<td class=.+?>.+?>(?<pitRefilledTo>[0-9]+?) .+?</td>.*?'
        . '<td class=.+?>(?<pitTime>.+?)</td>.*?'
        . '</tr>|is';
        if (preg_match_all($pattern, $pitStops, $mPitStops)) {
            $this->race['pitstops'] = [];

            foreach ($mPitStops['pitLap'] as $i => $lap) {
                $this->race['pitstops'][$i] = [
                    'lap' => (int) $lap,
                    'reason' => $mPitStops['pitReason'][$i],
                    'tyres' => (int) $mPitStops['pitTyres'][$i],
                    'fuel' => (int) $mPitStops['pitFuel'][$i],
                    'refilled_to' => (int) $mPitStops['pitRefilledTo'][$i],
                    'time' => trim(strip_tags($mPitStops['pitTime'][$i])),
                ];
            }
        }

        $blockAfterPitstops = $matches['blockAfterPitStops'];
        $mAfterPitStops = [];
        $pattern = '|<p>.+?>(?<finishTyres>[0-9]+?)%<.+?</p>.*?'
            . '<p>.+?>(?<finishFuel>[0-9]+?) .+?</p>|is';
        if (preg_match($pattern, $blockAfterPitstops, $mAfterPitStops)) {
            $this->race['finish_tyres'] = (int) $mAfterPitStops['finishTyres'];
            $this->race['finish_fuel'] = (int) $mAfterPitStops['finishFuel'];
        }

        $mAfterPitStops = [];
        $pattern = '|<tr>.*?'
            . '<td class=.+?>.+?&nbsp;(?<techProbLap>[0-9]+?)</td>.*?'
            . '<td class=.+?>(?<techProb>.+?)</td>.*?'
            . '</tr>|is';
        if (preg_match_all($pattern, $blockAfterPitstops, $mAfterPitStops)) {
            $this->race['tech_problems'] = [];

            foreach ($mAfterPitStops['techProbLap'] as $i => $lap) {
                $this->race['tech_problems'][$i] = [
                    'lap' => (int) $lap,
                    'reason' => $mAfterPitStops['techProb'][$i],
                ];
            }
        }
    }
}
