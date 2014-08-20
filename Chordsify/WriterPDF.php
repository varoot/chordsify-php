<?php
namespace Chordsify;

class WriterPDF extends Writer
{
    use PDFStyle;

    public $options = [
        'x'          => NULL,
        'y'          => NULL,
        'collapse'   => 0,
        'condensing' => 100,
        'formatted'  => true,
        'style'      => 'center',
    ];

    protected $songSheet;

    // Origin
    protected $left;
    protected $top;

    // Current position related to origin
    protected $x = 0;
    protected $y = 0;

    protected $firstParagraph = true;
    protected $maxCollapse;

    public function __construct(\TCPDF $pdf, array $options = [])
    {
        $this->pdf = $pdf;
        $this->options = array_merge($this->options, $options);
        $this->loadStyleSheet($this->options['style']);

        if (is_null($this->options['y'])) {
            $this->top = $this->pdf->GetY();
        } else {
            $this->top = (int) $this->options['y'];
        }

        if (is_null($this->options['x'])) {
            $this->left = $this->pdf->GetX();
        } else {
            $this->left = (int) $this->options['x'];
        }

        if ($this->options['collapse'] === 'all') {
            $this->widths = $this->heights = [0, 0];
        } else {
            $this->widths = $this->heights = 0;
        }
    }

    public function initSong(Song $song)
    {
        $this->pdf->setFontStretching(100);
        $this->setStyle('title');
        $this->writeCell($this->x, $this->y + $this->style['lineOffset'], $song->title);
        $this->pdf->setFontStretching($this->options['condensing']);
        $this->moveY($this->style['lineHeight']);

        $this->maxCollapse = $song->maxCollapseLevel();
    }

    public function song(Song $song, array $sections) {
        $output = null;

        foreach ($sections as $s) {
            if ($output === null) {
                $output = $s;
                continue;
            }

            for ($i = 0; $i <= $this->maxCollapse; $i++) {
                $output[$i]['width'] = max($output[$i]['width'], $s[$i]['width']);
                $output[$i]['height'] += $s[$i]['height'];
                $output[$i]['condensing'] = min($output[$i]['condensing'], $s[$i]['condensing']);
            }
        }

        $heightOffset = - $this->style['lineHeight'];
        $this->setStyle('title');
        $heightOffset += $this->style['lineHeight'];

        for ($i = 0; $i <= $this->maxCollapse; $i++) {
            $output[$i]['height'] += $heightOffset;
        }

        $m = $output[(int) $this->options['collapse']];

        /*
        $this->pdf->SetDrawColor(238, 102, 102);
        $this->pdf->Rect(
            $this->left, $this->top,
            $m['width'], $m['height']
        );
        */

        if ($this->options['collapse'] === 'all') {
            return $output;
        } else {
            return $output[$this->options['collapse']];
        }
    }

    public function initSection(Section $section)
    {
        $this->setStyle('lyrics.'.$section->type.'.prefix');

        if ( ! empty($this->style['text'])) {
            $y = $this->y + $this->style['lineOffset'];

            if ( ! $this->firstParagraph) {
                $y += $this->style['lineHeight'];
            }

            $width = $this->textWidth($this->style['text']);
            $this->writeCell($this->style['indent'] - $this->style['prefixMargin'] - $width, $y, $this->style['text']);
        }

        $this->setStyle('lyrics.'.$section->type);
    }

    public function section(Section $section, array $paragraphs) {
        $output = array_fill(0, $this->maxCollapse+1, [ 'width' => 0, 'height' => 0 ]);
        $output[0]['width']  = max(array_column($paragraphs, 'width'));
        $output[0]['height'] = array_sum(array_column($paragraphs, 'height'));

        foreach ($paragraphs as $p) {
            if ( ! isset($p['collapse'])) {
                $collapse = 0;
            } else {
                $collapse = $p['collapse']['saves'];
            }

            for ($i = 1; $i <= $collapse; $i++) {
                $output[$i]['width'] = max($output[$i]['width'], $p['collapse']['width']);
                $output[$i]['height'] += $p['collapse']['height'];
            }

            for ($i = $collapse+1; $i <= $this->maxCollapse; $i++) {
                $output[$i]['width'] = max($output[$i]['width'], $p['width']);
                $output[$i]['height'] += $p['height'];
            }
        }

        $columnWidth = $this->style['columnWidth'] - $this->style['indent'];
        for ($i = 0; $i <= $this->maxCollapse; $i++) {
            $output[$i]['condensing'] = min(100, (int) ($columnWidth * 100 / $output[$i]['width']));
            $output[$i]['width'] += $this->style['indent'];
        }

        return $output;
    }

    public function initParagraph(Paragraph $paragraph)
    {
        if ($this->firstParagraph) {
            $this->firstParagraph = false;
        } else {
            $this->moveY($this->style['lineHeight']);
        }
    }

    public function paragraph(Paragraph $paragraph, array $lines) {
        $lines = array_filter($lines);
        $width = 0;

        foreach ($lines as $text) {
            $width = max($width, $this->textWidth($text));
        }

        $output = [ 'width' => $width, 'height' => (count($lines)+1) * $this->style['lineHeight'] ];

        $collapse = $paragraph->collapse();
        if ($this->options['collapse'] > 0 and $collapse->saves >= $this->options['collapse']) {
            array_splice($lines, $collapse->lines, $collapse->saves);
            $lines[$collapse->lines-1] .= " (×{$collapse->times})";
            $collapse = get_object_vars($collapse);
            $collapse['width'] = max($width, $this->textWidth($lines[$collapse['lines']-1]));
            $collapse['height'] = (count($lines)+1) * $this->style['lineHeight'];
            $output['collapse'] = $collapse;
        }

        foreach ($lines as $text) {
            $this->writeCell($this->x, $this->y + $this->style['lineOffset'], $text);
            $this->moveY($this->style['lineHeight']);
        }

        return $output;
    }

    public function initLine(Line $line)
    {
        if ($line->chordsOnly) {
            return false;
        }

        $this->x = $this->style['indent'];
    }

    public function line(Line $line, array $words)
    {
        return implode($words);
    }

    public function word(Word $word, array $chunks)
    {
        return ltrim(implode($chunks));
    }

    public function chunk(Chunk $chunk, $chord, $lyrics)
    {
        return $lyrics;
    }

    public function initChord(Chord $chord) {
        return false;
    }

    public function chord(Chord $chord, array $chordElements) {}

    public function chordRoot(ChordRoot $chordRoot) {}

    public function chordText(ChordText $chordText) {}

    public function lyrics(Lyrics $lyrics)
    {
        $text = $this->options['formatted'] ? $lyrics->formattedContent() : $lyrics->content;
        return $text;
    }

    protected function textWidth($text)
    {
        return $this->pdf->GetStringWidth($text);
    }

    public function pdf()
    {
        return $this->pdf;
    }

    public function moveY($amount)
    {
        $this->y += $amount;
        return $this;
    }

    public function writeCell($x, $y, $text)
    {
        $this->pdf->SetY($this->top + $y);
        $this->pdf->SetX($this->left + $x);
        $this->pdf->Cell(
            0,                     // width
            0,                     // height
            $text,                 // text
            0,                     // border
            0,                     // cursor after
            $this->style['align'], // align
            false,                 // fill
            '',                    // link
            1,                     // stretch
            true,                  // ignore min-height
            'L'                    // align cell to font baseline
        );
    }
}
