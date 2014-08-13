<?php
namespace Chordsify;

abstract class Text {
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

    protected function textBefore(array $options = null)
    {
        return '';
    }

    protected function textAfter(array $options = null)
    {
        return '';
    }

    protected function htmlBefore(array $options = null)
    {
        return '';
    }

    protected function htmlAfter(array $options = null)
    {
        return '';
    }

    public function text(array $options = null)
    {
        $output = $this->textBefore($options);

        foreach ($this->children as $child) {
            $output .= $child->text($options);
        }

        $output .= $this->textAfter($options);

        return $output;
    }

    public function html(array $options = null)
    {
        $output = $this->htmlBefore($options);

        foreach ($this->children as $child) {
            $output .= $child->html($options);
        }

        $output .= $this->htmlAfter($options);

        return $output;
    }

    public function transpose($target_key)
    {
        foreach ($this->children as $child) {
            $child->transpose($target_key);
        }
        return $this;
    }

    public function __toString()
    {
        return $this->text();
    }
}
