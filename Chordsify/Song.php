<?php
namespace Chordsify;

class Song extends Unit
{
    protected $original_key;
    public $title;

    function __construct($raw = '', array $options = [])
    {
        if (isset($options['original_key'])) {
            $o_key = $options['original_key'];
        } else {
            $o_key = null;
        }

        $this->original_key = new Key($o_key);
        $this->title = @$options['title'];
        parent::__construct($raw, $options);
    }

    public function parse($raw = '', array $options = [])
    {
        $data = preg_split('/^\s*\[\s*('.implode('|', Config::$sections).')\s*(\d*)\s*\]\s*$/m', $raw, null, PREG_SPLIT_DELIM_CAPTURE);

        for ($i=0; $i < count($data); $i+=3) {
            if ($i==0 and trim($data[$i]) == '') {
                // Skip empty section at the beginning
                continue;
            }

            $this->children[] = new Section($data[$i], array(
                'song' => $this,
                'type' => $i > 0 ? $data[$i-2] : null,
                'number' => $i > 0 ? $data[$i-1] : null,
            ));
        }

        return $this;
    }

    public function originalKey()
    {
        return $this->original_key;
    }

    public function transpose($target_key)
    {
        $target_key = Key::value($target_key);
        return parent::transpose($target_key);
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
