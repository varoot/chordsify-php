<?php
namespace Chordsify;

class Chord extends Unit
{
    public $main_root;

    public function parse($raw = '', array $options = [])
    {
        $data = preg_split('/([A-G](?:#|b)?)/', $raw, null, PREG_SPLIT_DELIM_CAPTURE);

        for ($i = 0; $i < count($data); $i+=2) {
            if ($i == 0 and $data[$i] == '')
                continue;

            if ($i > 1) {
                $root = new ChordRoot($data[$i-1], $this);
                if ( ! isset($this->main_root))
                {
                    $this->main_root = $root;
                }

                $this->children[] = $root;
            }

            if ($data[$i] != '') {
                $this->children[] = new ChordText($data[$i], $this);
            }
        }

        return $this;
    }
}
