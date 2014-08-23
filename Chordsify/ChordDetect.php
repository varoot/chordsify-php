<?php
namespace Chordsify;

class ChordDetect
{
    // Average percentages of chords
    static $averages = [
        0.2910191470318945, 0.0,
        0.0, 0.0,
        0.002069835718444329, 0.05663182016879581,
        0.0, 0.0,
        0.0006566467240624544, 0.006913380218300791,
        0.26474368113211066, 0.0,
        0.0, 0.0,
        0.23113611813750268, 0.0,
        0.00032102728731942215, 0.0,
        0.0, 0.14307735154635395,
        0.003430992035215205, 0.0,
        0.0, 0.0,
    ];

    // Standard deviation
    static $sds = [
        0.10975312018945567, 0.0001,
        0.0001, 0.0001,
        0.011866502515340991, 0.06921016039067815,
        0.0001, 0.0001,
        0.004438928496835608, 0.028709432476276615,
        0.07517327700259886, 0.0001,
        0.0001, 0.0001,
        0.09572614262560347, 0.0001,
        0.0030115028955527644, 0.0001,
        0.0001, 0.09622134984681673,
        0.014570513766711314, 0.0001,
        0.0001, 0.0001,
    ];

    // Probability that this chord is not the first chord in a section
    static $notFirstProbs = [
        0.35964912280701755, 1.0,
        1.0, 1.0,
        1.0, 0.9517543859649122,
        1.0, 1.0,
        1.0, 0.9912280701754386,
        0.6929824561403508, 1.0,
        1.0, 1.0,
        0.956140350877193, 1.0,
        1.0, 1.0,
        1.0, 0.8377192982456141,
        0.9912280701754386, 1.0,
        1.0, 1.0,
    ];

    protected static function collectChords(Song $song)
    {
        $songChords = [];
        $firstChords = [];
        foreach ($song->children() as $section) {
            $sectionChords = [];
            if ( ! $section->hasChords)
                continue;

            // Collecting chords
            foreach ($section->children() as $paragraph) {
                if ( ! $paragraph->hasChords)
                    continue;
                foreach ($paragraph->children() as $line) {
                    foreach ($line->children() as $word) {
                        foreach ($word->children() as $chunk) {
                            $children = $chunk->children();
                            if ( ! empty($children['chord']))
                                $sectionChords[] = $children['chord'];
                        }
                    }
                }
            }

            $songChords += $sectionChords;
            $firstChords[] = $sectionChords[0];
        }

        return [$songChords, $firstChords];
    }

    public static function countArray($chords)
    {
        $arr = array_fill(0, 24, 0);
        foreach ($chords as $chord) {
            $type = $chord->type();

            if ($type != Chord::MAJOR and $type != Chord::MINOR and $type != Chord::SUSTAINED)
                continue; // Skip other types of chord

            if ($type == Chord::MINOR) {
                $type = 1;
            } else {
                $type = 0;
            }

            $arr[$chord->value()*2+$type]++;
        }
        return $arr;
    }

    public static function percentageArray($chords)
    {
        $total = count($chords);
        $arr = self::countArray($chords);

        if ($total == 0)
            return $arr;

        return array_map(function($x) use ($total) { return $x/$total; }, $arr);
    }

    public static function rotateArray(&$arr, $count = 1)
    {
        $items = array_splice($arr, 0, $count);
        $arr = array_merge($arr, $items);
    }

    public static function calculateZ($values, $averages, $sds)
    {
        $z = 0;
        foreach ($values as $i => $value) {
            $z += abs($value - $averages[$i])/$sds[$i];
        }
        return $z;
    }

    public static function calculateProb($counts, $probs)
    {
        $prob = 1;
        foreach ($counts as $i => $count) {
            $prob *= pow($probs[$i], $count);
        }
        return $prob;
    }

    public static function evaluateKey($key, $chords, $firsts)
    {
        return self::calculateZ($chords, self::$averages, self::$sds)
            + self::calculateProb($firsts, self::$notFirstProbs) * 10;
    }

    public static function detectKey(Song $song)
    {
        list($songChords, $firstChords) = self::collectChords($song);

        // Cannot detect key if there's no chord
        if (count($songChords) == 0) {
            return null;
        }

        $chordPercentages = self::percentageArray($songChords);
        $firstCounts = self::countArray($firstChords);

        // Try each key
        $bestScore = null;
        $bestKey = null;
        for ($key = 0; $key < 12; $key++) {
            $score = self::evaluateKey($key, $chordPercentages, $firstCounts);
            if ($bestScore === null or $bestScore > $score) {
                $bestScore = $score;
                $bestKey = $key;
            }
            self::rotateArray($chordPercentages, 2);
            self::rotateArray($firstCounts, 2);
        }

        return new Key($bestKey);
    }
}
