<?php
namespace Chordsify;

class Word extends Unit
{
    public $hasChords = false;

    public function parse($raw = '', array $options = [])
    {
        $data = preg_split('/\[([^\]]*)\]/', $raw, null, PREG_SPLIT_DELIM_CAPTURE);

        for ($i=0; $i < count($data); $i+=2) {
            if ($i==0 and $data[$i] == '')
                continue;

            $options = [];

            if ($i > 0) {
                $options['chord'] = $data[$i-1];
                $this->hasChords = true;
            }

            $this->children[] = new Chunk($data[$i], $this, $options);
        }

        if (count($this->children) > 0) {
            $lastChunk = $this->children[count($this->children)-1];
            $lastChunk->last = true;
        }

        return $this;
    }
}
