<?php
namespace Chordsify;

class Chunk extends Unit
{
    public $chord;
    public $lyrics;

    public function parse($raw = '', array $options = [])
    {
        if ( ! empty($options['chord'])) {
            $this->chord = new Chord($options['chord']);
        }

        $this->lyrics = new Lyrics($raw);
    }
}
