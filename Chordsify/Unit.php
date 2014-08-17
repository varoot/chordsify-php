<?php
namespace Chordsify;

abstract class Unit {
    protected $children = array();
    protected $song;

    abstract public function parse($raw = '', array $options = []);

    // Note: $parent is optional for Song
    public function __construct($raw = '', $parent = null, array $options = [])
    {
        if ($parent instanceof Unit) {
            if ($parent instanceof Song) {
                $this->song = $parent;
            } elseif ($parent->song instanceof Song) {
                $this->song = $parent->song;
            }
        } else {
            if (is_array($parent) and empty($options)) {
                // Assuming parent is skipped
                $options = $parent;
            }
        }

        $this->parse($raw, $options);
    }

    public function transpose($target_key)
    {
        foreach ($this->children as $child) {
            $child->transpose($target_key);
        }
        return $this;
    }

    public function write(Writer $writer)
    {
        $unitName = end(explode('\\', get_class($this)));
        $writer->{'init'.$unitName}($this);

        $unitName = lcfirst($unitName);

        if ($this instanceof UnitLeaf)
            return $writer->$unitName($this);

        $children = array();
        foreach ($this->children as $child)
        {
            $children[] = $child->write($writer);
        }

        return $writer->$unitName($this, $children);
    }

    public function __toString()
    {
        return $this->text();
    }
}
