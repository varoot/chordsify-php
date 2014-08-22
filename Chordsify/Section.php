<?php
namespace Chordsify;

class Section extends Unit
{
    public $hasChords = false;
    public $type = '';
    public $number = 0;

    public function parse($raw = '', array $options = [])
    {
        $this->type = (string) $options['type'];
        $this->number = (int) $options['number'];

        $data = array_filter(preg_split('/(\s*\n){2}/', $raw));

        foreach ($data as $p) {
            $p = new Paragraph(trim($p), $this);
            $this->children[] = $p;
            $this->hasChords |= $p->hasChords;
        }

        return $this;
    }
}
