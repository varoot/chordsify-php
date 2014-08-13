<?php
namespace Chordsify;

class ChordText extends Text
{
    public $content = '';

    public function parse($raw = '', array $options = null)
    {
        $this->content = $raw;
        return $this;
    }

    public function text(array $options = null)
    {
        return $this->content;
    }

    public function html(array $options = null)
    {
        return htmlspecialchars($this->content);
    }
}
