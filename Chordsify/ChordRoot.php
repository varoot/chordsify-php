<?php
namespace Chordsify;

class ChordRoot extends UnitLeaf
{
    public $root;
    public $relative_root;

    public function parse($raw = '', array $options = [])
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
}
