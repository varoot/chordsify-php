<?php
namespace Chordsify;

class Paragraph extends Unit
{
    public $hasChords = true;
    public $collapse;
    
    // whether this paragraph contains only chords
    public $chordsOnly = true;

    public function parse($raw = '', array $options = [])
    {
        if (strpos($raw, '[') === false) {
            $this->hasChords = false;
        }

        $data = preg_split('/\n/', $raw);

        foreach ($data as $l) {
            $line = new Line($l, $this);
            $this->children[] = $line;
            $this->chordsOnly &= $line->chordsOnly;
        }

        return $this;
    }

    protected function findCollapse()
    {
        $lines = [];
        foreach ($this->children as $line) {
            $lineText = $line->text(['chords' => false, 'formatted' => true]);
            if (trim($lineText) != '') {
                $lines[] = $lineText;
            }
        }

        $maxRepeat = $foundLines = $foundTimes = 0;

        for ($r = (int) (count($lines)/2); $r > 0; $r--) {
            // Testing for $r-line repeat, e.g. 2-line repeat (AABAAB), 2-line repeat (ABABC), etc.
            // Find how many times it repeats first $r lines.
            for ($times = 1; $times < (int) (count($lines) / $r); $times++) {
                // Check all the lines to make sure it's repeated
                $repeat = true;
                for ($i = 0; $i < $r; $i++) {
                    if ($lines[$i] != $lines[$i+($r*$times)]) {
                        $repeat = false;
                        break;
                    }
                }

                // Not all lines are repeated
                if ( ! $repeat)
                    break;
            }

            if ($times > 1 and $r*$times >= $maxRepeat) {
                $foundLines = $r;
                $foundTimes = $times;
                $maxRepeat = $r*$times;
            }
        }

        return (object) ['lines' => $foundLines, 'times' => $foundTimes, 'saves' => $foundLines*($foundTimes-1)];
    }

    // Cache collapse data
    public function collapse()
    {
        if (empty($this->collapse))
        {
            $this->collapse = $this->findCollapse();
        }

        return $this->collapse;
    }
}
