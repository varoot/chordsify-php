<?php
namespace Chordsify;

class Paragraph extends Unit
{
    public $chord_exists = true;

    public function parse($raw = '', array $options = [])
    {
        if (strpos($raw, '[') === false) {
            $this->chord_exists = false;
        }

        $data = preg_split('/\n/', $raw);

        foreach ($data as $l) {
            $this->children[] = new Line($l, array('song'=>$this->song));
        }

        return $this;
    }
}
