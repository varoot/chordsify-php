<?php
namespace Chordsify;

class ChordRoot extends Text
{
    public $root;
    public $relative_root;

    public function parse($raw = '', array $options = null)
    {
        $this->root = new Key($raw);
        $this->relative_root = $this->root->relativeTo($this->song->originalKey());
        return $this;
    }

    public function transpose($target_key)
    {
        $this->root->set(($target_key + $this->relative_root) % 12);
        return $this;
    }

    public function text(array $options = null)
    {
        return $this->root;
    }

    public function html(array $options = null)
    {
        return Config::tag('chordRoot', $this->root, array(
            Config::$data_attr['chordRel'] => $this->root->relativeTo($this->song->originalKey()),
        ));
    }
}
