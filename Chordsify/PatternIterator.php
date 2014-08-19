<?php
namespace Chordsify;

class PatternIterator implements \Iterator
{
    protected $pattern = [];
    protected $keys = [];
    protected $values = [];
    protected $position;
    protected $outputValue = false;

    public function __construct(array $pattern, $outputValue = false)
    {
        $this->keys = array_keys($pattern);

        $patternValues = array_values($pattern);

        $this->pattern = array_map(function($x) {
            if (is_array($x)) {
                return array_values($x);
            } elseif (is_int($x) or (is_numeric($x) and (int) $x == $x)) {
                return range(0, $x-1);
            } else {
                return [ $x ];
            }
        }, $patternValues);
        $this->outputValue = $outputValue;
        $this->rewind();
    }

    public function current()
    {
        $arr = [];
        for ($i = 0; $i < count($this->pattern); $i++) {
            $arr[$this->keys[$i]] = $this->pattern[$i][$this->values[$i]];
        }

        if ( ! $this->outputValue) {
            return $arr;
        } else {
            $valArray = [];
            $values = array_values($arr);
            sort($values);
            foreach ($values as $v) {
                $valArray[$v] = [];
            }
            foreach ($arr as $key => $value) {
                $valArray[$value][] = $key;
            }
            return $valArray;
        }
    }

    public function key()
    {
        return implode('.', $this->values);
    }

    public function next()
    {
        while ($this->position >= 0 and $this->values[$this->position] >= count($this->pattern[$this->position])-1) {
            $this->position--;
        }

        if ($this->position >= 0) {
            $this->values[$this->position]++;
            for ($i = $this->position+1; $i < count($this->pattern); $i++) {
                $this->values[$i] = 0;
            }
            $this->position = count($this->pattern)-1;
            $this->valid = true;
        } else {
            $this->valid = false;
        }
    }

    public function rewind()
    {
        $this->position = count($this->pattern)-1;
        $this->values = array_fill(0, count($this->pattern), 0);
    }

    public function valid()
    {
        return ($this->position >= 0);
    }
}
