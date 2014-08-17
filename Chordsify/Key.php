<?php
namespace Chordsify;

class Key
{
    static public $sharp_keys = ['C', 'C#', 'D', 'D#', 'E', 'F', 'F#', 'G', 'G#', 'A', 'A#', 'B'];
    static public $flat_keys = ['C', 'Db', 'D', 'Eb', 'E', 'F', 'Gb', 'G', 'Ab', 'A', 'Bb', 'B'];
    static public $flat_scales = [0, 1, 3, 5, 8, 10];
    static public $map = [
        "Cb" => 11, "C"  => 0,  "C#" => 1,
        "Db" => 1,  "D"  => 2,  "D#" => 3,
        "Eb" => 3,  "E"  => 4,  "E#" => 5,
        "Fb" => 4,  "F"  => 5,  "F#" => 6,
        "Gb" => 6,  "G"  => 7,  "G#" => 8,
        "Ab" => 8,  "A"  => 9,  "A#" => 10,
        "Bb" => 10, "B"  => 11, "B#" => 0,
    ];
    protected $value = null;

    public static function value($k)
    {
        if (is_null($k))
            return null;

        if (is_numeric($k))
            return (int) $k % 12;

        if (is_object($k) and get_class($k) == __CLASS__)
            return $k->value;

        $k = ucwords(trim($k));

        if (array_key_exists($k, self::$map))
            return self::$map[$k];

        // Invalid key
        return null;
    }

    public function text($flat = false)
    {
        $keys = $flat ? self::$flat_keys : self::$sharp_keys;
        return $keys[$this->value];
    }

    public function formatted_text($flat = false)
    {
        return str_replace(array_keys(Config::$chars), array_values(Config::$chars), $this->text($flat));
    }

    public function set($k)
    {
        $k = self::value($k);
        $this->value = $k;

        return $this;
    }

    public function relativeTo($original_key)
    {
        if (is_null($this->value) or is_null($original_key->value)) {
            return $this->value;
        }

        return ((12 + $this->value) - $original_key->value) % 12;
    }

    // true  = this is a flat scale
    // false = this is a sharp scale
    public function isFlatScale()
    {
        return in_array($this->value, self::$flat_scales);
    }

    function __construct($k)
    {
        $this->set($k);
    }

    public static function factory($k)
    {
        return new self($k);
    }

    public function __toString()
    {
        return $this->text($this->value);
    }
}
