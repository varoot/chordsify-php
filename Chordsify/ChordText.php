<?php
namespace Chordsify;

class ChordText extends UnitLeaf
{
    public $content = '';

    public function parse($raw = '', array $options = [])
    {
        $this->content = $raw;
        return $this;
    }
}
