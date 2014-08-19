<?php
namespace Chordsify;

class Line extends Unit
{
    public $isTooLong = false;

    // whether this line contains only chords
    public $chordsOnly = false;

    public function parse($raw = '', array $options = [])
    {
        preg_match_all('/[^\s\[\]]*(\[[^\]]*\][^\s\[\]]*)*\s*/', trim($raw), $matches);

        foreach ($matches[0] as $word) {
            // Skip blank words
            if ($word == '')
                continue;

            $this->children[] = new Word($word, $this);
        }

        preg_match_all('/([^\s\[\]]*)(?:(?:\[[^\]]*\])([^\s\[\]]*))*\s*/', trim($raw), $matches);
        if (trim(implode($matches[1]).implode($matches[2])) == '') {
            $this->chordsOnly = true;
        }

        return $this;
    }
}
