<?php
namespace Chordsify;

class Chunk extends Unit
{
    public function parse($raw = '', array $options = null)
    {
        $this->children['lyrics'] = new Lyrics($raw, array('song'=>$this->song));

        if ( ! empty($options['chord'])) {
            $this->children['chord'] = new Chord($options['chord'], array('song'=>$this->song));
        }

        return $this;
    }

    public function write($writer)
    {
        if (isset($this->children['chord'])) {
            $chord = $this->children['chord']->write($writer);
        } else {
            $chord = NULL;
        }

        $lyrics = $this->children['lyrics']->write($writer);
        return $writer->chunk($this, $chord, $lyrics);
    }
}
