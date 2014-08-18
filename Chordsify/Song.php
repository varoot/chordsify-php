<?php
namespace Chordsify;

class Song extends Unit
{
    protected $originalKey;
    public $title;

    public function parse($raw = '', array $options = [])
    {
        $options = array_merge(['originalKey'=>NULL, 'title'=>''], $options);

        $this->originalKey = new Key($options['originalKey']);
        $this->title = $options['title'];

        $data = preg_split('/^\s*\[\s*('.implode('|', Config::$sections).')\s*(\d*)\s*\]\s*$/m', $raw, null, PREG_SPLIT_DELIM_CAPTURE);

        for ($i=0; $i < count($data); $i+=3) {
            // Skip empty section at the beginning
            if ($i==0 and trim($data[$i]) == '')
                continue;

            $this->children[] = new Section($data[$i], $this, [
                'type' => $i > 0 ? $data[$i-2] : null,
                'number' => $i > 0 ? $data[$i-1] : null,
            ]);
        }

        return $this;
    }

    public function originalKey()
    {
        return $this->originalKey;
    }

    public function transpose($targetKey)
    {
        $targetKey = Key::value($targetKey);
        return parent::transpose($targetKey);
    }

    public function text(array $options = [])
    {
        return $this->write(new WriterText($options));
    }

    public function html(array $options = [])
    {
        return $this->write(new WriterHTML($options));
    }
}
