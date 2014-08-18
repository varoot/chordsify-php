<?php
namespace Chordsify;

class Paragraph extends Unit
{
    public $hasChords = true;

    public function parse($raw = '', array $options = [])
    {
        if (strpos($raw, '[') === false) {
            $this->hasChords = false;
        }

        $data = preg_split('/\n/', $raw);

        foreach ($data as $l) {
            $this->children[] = new Line($l, $this);
        }

        return $this;
    }
}
