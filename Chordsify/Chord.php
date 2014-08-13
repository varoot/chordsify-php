<?php
namespace Chordsify;

class Chord extends Text
{
    public $main_root;

    public function parse($raw = '', array $options = null)
    {
        $data = preg_split('/([A-G](?:#|b)?)/', $raw, null, PREG_SPLIT_DELIM_CAPTURE);

        for ($i = 0; $i < count($data); $i+=2) {
            if ($i == 0 and $data[$i] == '')
                continue;

            if ($i > 1) {
                $root = new ChordRoot($data[$i-1], array('song'=>$this->song));
                if ( ! isset($this->main_root))
                {
                    $this->main_root = $root;
                }

                $this->children[] = $root;
            }

            if ($data[$i] != '') {
                $this->children[] = new ChordText($data[$i], array('song'=>$this->song));
            }
        }

        return $this;
    }

    public function textBefore(array $options = null)
    {
        return '[';
    }

    public function textAfter(array $options = null)
    {
        return ']';
    }

    public function htmlBefore(array $options = null)
    {
        $attr = array();
        if ($this->main_root) {
            $attr[Config::$data_attr['chord']] = Key::value($this->main_root->root);
        }

        return Config::tagOpen('chordAnchor').Config::tagOpen('chord', $attr);
    }

    public function htmlAfter(array $options = null)
    {
        return Config::tagClose('chord').Config::tagClose('chordAnchor');
    }
}
