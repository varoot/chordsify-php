<?php
namespace Chordsify;

class Paragraph extends Unit
{
    public $chord_exists = true;

    public function parse($raw = '', array $options = [])
    {
        if (strpos($raw, '[') === false) {
            $this->chord_exists = false;
        }

        $data = preg_split('/\n/', $raw);

        foreach ($data as $l) {
            $this->children[] = new Line($l, array('song'=>$this->song));
        }

        return $this;
    }

    protected static function findCollapse($lines)
    {
        $max_repeat = 0;
        $found_lines = 0;
        $found_times = 0;

        for ($r = (int) (count($lines)/2); $r > 0; $r--) {
            // Testing for $r-line repeat, e.g. 2-line repeat (AABAAB), 2-line repeat (ABABC), etc.
            // Find how many times it repeats first $r lines.
            for ($times = 1; $times < (int) (count($lines) / $r); $times++) {
                // Check all the lines to make sure it's repeated
                $repeat = true;
                for ($i = 0; $i < $r; $i++) {
                    if (trim($lines[$i]) != trim($lines[$i+($r*$times)])) {
                        $repeat = false;
                        break;
                    }
                }

                if ( ! $repeat) {
                    // Not all lines are repeated
                    break;
                }
            }

            if ($times > 1 and $r*$times >= $max_repeat) {
                $found_lines = $r;
                $found_times = $times;
                $max_repeat = $r*$times;
            }
        }

        return array($found_lines, $found_times);
    }

    public function text(array $options = [])
    {
        $output = parent::text($options);

        if (trim($output) == '') {
            // Prevent returning an empty paragraph
            return '';
        }

        if ( ! isset($options['collapse']) or ! $options['collapse'])
            return $output;

        $lines = explode("\n", $output);

        // Remove last blank line
        unset($lines[count($lines)-1]);
        unset($lines[count($lines)-1]);

        list($repeat, $times) = $this->findCollapse($lines);

        if ($repeat) {
            array_splice($lines, $repeat, $repeat * ($times-1));
            $lines[$repeat-1] .= " (×$times)";
            $output = implode("\n", $lines)."\n\n";
        }

        return $output;
    }
}
