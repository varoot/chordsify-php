<?php
namespace Chordsify;

class Song extends Unit
{
    protected $originalKey;

    // Title and ID are optional
    public $title;
    public $id;

    // Object hash
    public $hash;
    public $hasChords = false;

    public function parse($raw = '', array $options = [])
    {
        $this->hash = spl_object_hash($this);

        $options = array_merge(['originalKey'=>null, 'title'=>'', 'id'=>''], $options);

        $this->originalKey = new Key($options['originalKey']);
        $this->title = $options['title'];
        $this->id = $options['id'];

        $data = preg_split('/^\s*\[\s*('.implode('|', Config::$sections).')\s*(\d*)\s*\]\s*$/m', $raw, null, PREG_SPLIT_DELIM_CAPTURE);

        for ($i=0; $i < count($data); $i+=3) {
            // Skip empty section at the beginning
            if ($i==0 and trim($data[$i]) == '')
                continue;

            $s = new Section($data[$i], $this, [
                'type' => $i > 0 ? $data[$i-2] : null,
                'number' => $i > 0 ? $data[$i-1] : null,
            ]);
            $this->children[] = $s;
            $this->hasChords |= $s->hasChords;
        }

        if ($this->originalKey->value() === null and $options['originalKey'] == 'auto') {
            $this->detectKey(true);
        }

        return $this;
    }

    public function detectKey($setKey = false)
    {
        $key = ChordDetect::detectKey($this);
        if ($setKey) {
            $this->originalKey($key);
        }
        return $key;
    }

    // Getter and setter
    public function originalKey($key = null)
    {
        if ($key == null) {
            return $this->originalKey;
        }

        $this->originalKey = new Key($key);

        // Re-calculate relative value for chords
        foreach ($this->children() as $section) {
            if ( ! $section->hasChords) continue;
            foreach ($section->children() as $paragraph) {
                if ( ! $paragraph->hasChords) continue;
                foreach ($paragraph->children() as $line) {
                    foreach ($line->children() as $word) {
                        foreach ($word->children() as $chunk) {
                            $children = $chunk->children();
                            if ( ! empty($children['chord'])) {
                                $children['chord']->calculateRelativeValue();
                            }
                        }
                    }
                }
            }
        }

        return $this;
    }

    public function transpose($targetKey)
    {
        $targetKey = Key::filter_value($targetKey);
        return parent::transpose($targetKey);
    }

    public function maxCollapseLevel()
    {
        $level = 0;
        foreach ($this->children() as $section) {
            foreach ($section->children() as $paragraph) {
                if ($paragraph->collapse()->saves > $level) {
                    $level = $paragraph->collapse()->saves;
                }
            }
        }
        return $level;
    }

    public function html(array $options = [])
    {
        return $this->write(new WriterHTML($options));
    }
}
