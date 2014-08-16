<?php
namespace Chordsify;

class ChordText extends Unit
{
    public $content = '';

    public function parse($raw = '', array $options = null)
    {
        $this->content = $raw;
        return $this;
    }

    public function write($writer)
    {
        return $writer->chordText($this);
    }
}
