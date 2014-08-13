<?php
namespace Chordsify;

class Line extends Text
{
    public function parse($raw = '', array $options = null)
    {
        preg_match_all('/[^\s\[\]]*(\[[^\]]*\][^\s\[\]]*)*\s*/', trim($raw), $matches);

        foreach ($matches[0] as $word) {
            $this->children[] = new Word($word, array('song'=>$this->song));
        }

        return $this;
    }

    protected function textAfter(array $options = null)
    {
        return "\n";
    }

    public function text(array $options = null)
    {
        $output = parent::text($options);

        if (trim($output) == '') {
            // Prevent returning an empty line
            return '';
        }

        return $output;
    }

    public function htmlBefore(array $options = null)
    {
        return Config::tagOpen('line');
    }

    public function htmlAfter(array $options = null)
    {
        return Config::tagClose('line');
    }
}
