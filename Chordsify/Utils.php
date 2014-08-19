<?php
namespace Chordsify;

class Utils
{
    public static function average(array $values) {
        return array_sum($values) / count($values);
    }

    public static function variance(array $values) {
        $average = self::average($values);
        $variance = 0;
        foreach ($values as $v) {
            $variance += pow($v - $average, 2);
        }
        return $variance/count($values);
    }

    public static function sd(array $values) {
        return sqrt(self::variance($values));
    }

    protected static function compare($a, $b, $preferMax) {
        if ($preferMax) {
            return ($a > $b) - ($a < $b);
        } else {
            return ($a < $b) - ($a > $b);
        }
    }

    public static function compareDominance(array $a, array $b, array $keys) {
        $cmp = 0;
        foreach ($keys as $key => $preferMax) {
            if ($cmp == 0) {
                $cmp = self::compare($a[$key], $b[$key], $preferMax);
            } else {
                $newCmp = self::compare($a[$key], $b[$key], $preferMax);
                if ($newCmp != 0 and $newCmp != $cmp)
                    return 0;
            }
        }
        return $cmp;
    }

    public static function removeDominated(array $arr, array $keys) {
        for ($i = 0; $i < count($arr)-1; $i++) {
            for ($j = $i+1; $j < count($arr); $j++) {
                // Compare I and J for each key
                $dominated = self::compareDominance($arr[$i], $arr[$j], $keys);
                if ($dominated == -1) {
                    array_splice($arr, $i, 1);
                    $i--;
                    break;
                } elseif ($dominated == 1) {
                    array_splice($arr, $j, 1);
                    $j--;
                }
            }
        }
        return $arr;
    }
}
