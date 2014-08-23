<?php
namespace Chordsify;

class Chord extends Unit
{
    const UNKNOWN = -1;
    const MAJOR = 0;
    const MINOR = 1;
    const SUSTAINED = 2;
    const AUGMENTED = 3;
    const DIMINISHED = 4;

    protected $mainRoot;
    protected $type;

    public function parse($raw = '', array $options = [])
    {
        $data = preg_split('/([A-G](?:#|b)?)/', $raw, null, PREG_SPLIT_DELIM_CAPTURE);

        for ($i = 0; $i < count($data); $i+=2) {
            if ($i == 0 and $data[$i] == '')
                continue;

            if ($i > 1) {
                $root = new ChordRoot($data[$i-1], $this);
                if ( ! isset($this->mainRoot)) {
                    $this->mainRoot = $root;
                    preg_match('/^(m|min|maj|Maj|M|aug|\+|dim|sus)/', $data[$i], $types);
                    if (count($types) == 0) {
                        $this->type = self::MAJOR;
                    } else {
                        if ($types[0] == 'M' or $types[0] == 'maj' or $types[0] == 'Maj') {
                            $this->type = self::MAJOR;
                        } elseif ($types[0] == 'm' or $types[0] == 'min') {
                            $this->type = self::MINOR;
                        } elseif ($types[0] == 'sus') {
                            $this->type = self::SUSTAINED;
                        } elseif ($types[0] == 'aug' or $types[0] == '+') {
                            $this->type = self::AUGMENTED;
                        } elseif ($types[0] == 'dim') {
                            $this->type = self::DIMINISHED;
                        } else {
                            // It shouldn't get here though
                            $this->type = self::UNKNOWN;
                        }
                    }
                }

                $this->children[] = $root;
            }

            if ($data[$i] != '') {
                $this->children[] = new ChordText($data[$i], $this);
            }
        }

        return $this;
    }

    public function calculateRelativeValue()
    {
        if ($this->mainRoot) {
            $this->mainRoot->calculateRelativeValue();
        }
        return $this;
    }

    public function value()
    {
        return $this->mainRoot->value();
    }

    public function type()
    {
        return $this->type;
    }
}
