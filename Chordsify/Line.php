<?php
namespace Chordsify;

class Line extends Unit
{
    public function parse($raw = '', array $options = [])
    {
        preg_match_all('/[^\s\[\]]*(\[[^\]]*\][^\s\[\]]*)*\s*/', trim($raw), $matches);

        foreach ($matches[0] as $word) {
            $this->children[] = new Word($word, $this);
        }

        return $this;
    }
}
