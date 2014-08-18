<?php
namespace Chordsify;

class Paragraph extends Unit
{
    public $chordExists = true;

    public function parse($raw = '', array $options = [])
    {
        if (strpos($raw, '[') === false) {
            $this->chordExists = false;
        }

        $data = preg_split('/\n/', $raw);

        foreach ($data as $l) {
            $this->children[] = new Line($l, $this);
        }

        return $this;
    }
}
