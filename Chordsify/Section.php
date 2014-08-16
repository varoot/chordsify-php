<?php
namespace Chordsify;

class Section extends Unit
{
    public $type = '';
    public $number = 0;

    public function parse($raw = '', array $options = [])
    {
        $this->type = (string) $options['type'];
        $this->number = (int) $options['number'];

        $data = array_filter(preg_split('/(\s*\n){2}/', $raw));

        foreach ($data as $p) {
            $this->children[] = new Paragraph(trim($p), array('song'=>$this->song));
        }

        return $this;
    }
}
