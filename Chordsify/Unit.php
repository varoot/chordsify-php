<?php
namespace Chordsify;

abstract class Unit {
    protected $children = array();
    protected $song;

    abstract public function parse($raw = '', array $options = null);

    public function __construct($raw = '', array $options = null)
    {
        if (isset($options['song'])) {
            $this->song = $options['song'];
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

    public function write($writer)
    {
        $unitName = lcfirst(end(explode('\\', get_class($this))));
        $children = array();
        if ( ! empty($this->children))
        {
            foreach ($this->children as $child)
            {
                $children[] = $child->write($writer);
            }
        }

        return $writer->$unitName($this, $children);
    }

    public function song()
    {
        return $this->song;
    }

    public function __toString()
    {
        return $this->text();
    }
}
