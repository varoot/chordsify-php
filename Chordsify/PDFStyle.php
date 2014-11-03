<?php
namespace Chordsify;

use Symfony\Component\Yaml\Yaml;

trait PDFStyle
{
    // Fonts loaded
    protected $fonts = [];

    // Stylesheet
    protected $styleSheet = [];

    // Style of current element
    protected $style = [];

    abstract public function pdf();

    public function loadStyleSheet($styleName)
    {
        $allStyles = Yaml::parse(realpath(__DIR__.'/'.Config::$pdfStyleSheet));

        if ( ! array_key_exists($styleName, $allStyles))
            return $allStyles['default'];

        $this->styleSheet = array_merge($allStyles['default'], $allStyles[$styleName]);

        return $this;
    }

    protected function addFont($font)
    {
        $fontFile = realpath(__DIR__.'/'.Config::$font_dir.$font);
        if (is_file($fontFile)) {
            return $this->fonts[$font] = $this->pdf()->addTTFfont($fontFile);
        }
        return $font;
    }

    public function setFont($font, $size)
    {
        // Check if font is loaded
        if (array_key_exists($font, $this->fonts)) {
            $font = $this->fonts[$font];
        } else {
            // Load font if exists
            $font = $this->addFont($font);
        }

        $style = substr($font, -1);
        if ($style == 'b' or $style == 'i') {
            if (substr($font, -2) == 'bi') {
                $style = 'bi';
                $font = substr($font, 0, -2);
            } else {
                $font = substr($font, 0, -1);
            }
        } else {
            $style = '';
        }

        $this->pdf()->SetFont($font, $style, $size);

        return $this;
    }

    public function setStyle($path)
    {
        $path = explode('.', $path);
        $style = $tree = $this->styleSheet;

        foreach ($path as $level) {
            if ( ! isset($tree[$level]))
                break;

            $tree = $tree[$level];
            $style = array_merge($style, $tree);
        }

        $this->style = array_filter($style, function($x) { return ! is_array($x); });

        if ($this->pdf()) {
            $this->setFont($this->style['font'], $this->style['fontSize']);
        }

        return $this;
    }
}
