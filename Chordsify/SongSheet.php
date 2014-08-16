<?php
namespace Chordsify;

class SongSheet
{
    public $songs = array();
    public $pdf;
    public $debug = false;

    // * = default value from Config file
    protected $copies;          // * Number of copies
    protected $page_size;       // * Size of the paper (e.g. A4 or Letter)
    protected $page_w;          // Page width
    protected $page_h;          // Page height
    protected $margin_top;      // Top margin
    protected $gutter;          // Column gutter
    protected $columns;         // * Number of columns per page
    protected $max_lines;       // Number of lines per column
    protected $fonts = array(); // Fonts used

    protected $styles;          // * will be loaded from yaml
    protected $style;           // Current style
    protected $line_height;     // For grid. This value is read from style

    protected $column;          // Current column
    protected $line;            // Current line to draw

    public function __construct(array $options = null)
    {
        $this->page_size = empty($options['size']) ? Config::$pdf_size : $options['size'];
        $this->copies = empty($options['copies']) ? Config::$pdf_copies : $options['copies'];
        $columns = empty($options['columns']) ? Config::$pdf_columns : (int) $options['columns'];
        $style = empty($options['style']) ? Config::$pdf_style : $options['style'];

        $this->styles = Config::loadStyle($style);
        $this->style = $this->getStyle('');

        // Initialize PDF
        $pdf = new \TCPDF('P', 'pt' /* unit */, $this->page_size, true, 'UTF-8', false);

        // Set up info
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Psalted.com');
        $pdf->setLanguageArray(array( // English
            'a_meta_charset'  => 'UTF-8',
            'a_meta_dir'      => 'ltr',
            'a_meta_language' => 'en',
            'w_page'          => 'page',
        ));

        // Read page dimensions
        $this->page_w = $pdf->getPageWidth();
        $this->page_h = $pdf->getPageHeight();

        // Calculate number of lines and actual margin
        $this->line_height = $line_height = $this->style['lineHeight'];
        $height = $this->page_h - (2 * Config::$pdf_margin);
        $this->max_lines = (int) ($height / $line_height);
        $this->margin_top = ($this->page_h - ($this->max_lines * $line_height)) / 2;

        // Set up page
        $pdf->setCellPaddings(0);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->setPageOrientation('P', false /* No auto page-break */, $this->margin_top - $this->styles['lineOffset'] /* root lineOffset*/);
        $cols = $this->calculateColumns($columns);

        // Set up columns
        $this->columns = $columns;
        $pdf->setColumnsArray($cols);
        $pdf->SetMargins($this->gutter, $this->margin_top);

        // Set up fonts
        $pdf->setFontSubsetting(false);

        $this->pdf = $pdf;
    }

    public function addPage()
    {
        $this->pdf->AddPage();

        if ($this->debug) {
            $this->drawGrid();
        }

        $this->pdf->selectColumn(0);
        $this->column = 0;
        $this->line = 0;
        return $this;
    }

    // Calculate column array
    protected function calculateColumns($columns)
    {
        $page_col_width = $this->page_w / $columns;
        $gutter = ($page_col_width - Config::$pdf_column_width) / 2;
        $this->gutter = $gutter;

        $cols = array();

        for ($i = 0; $i < $columns; $i++) {
            $cols[] = array(
                'w' => Config::$pdf_column_width,
                's' => ($i == $columns-1) ? $gutter : $gutter * 2,
                'y' => $this->margin_top,
            );
        }

        return $cols;
    }

    protected function setFont($font, $size)
    {
        // Check if font is loaded
        if (array_key_exists($font, $this->fonts)) {
            $font = $this->fonts[$font];
        } else {
            // Load font if exists
            if (is_file(__DIR__.'/'.Config::$font_dir.$font)) {
                $this->fonts[$font] = $this->pdf->addTTFfont(__DIR__.'/'.Config::$font_dir.$font);
                $font = $this->fonts[$font];
            }
        }

        $this->pdf->SetFont($font, '', $size);

        return $this;
    }

    protected function getStyle($path = '')
    {
        $path = explode('.', $path);
        $style = $tree = $this->styles;

        foreach ($path as $level) {
            if ( ! isset($tree[$level])) {
                break;
            }

            $tree = $tree[$level];
            $style = array_merge($style, $tree);
        }

        return array_filter($style, function($x) { return ! is_array($x); });
    }

    protected function setStyle($path)
    {
        $this->style = $this->getStyle($path);
        $this->setFont($this->style['font'], $this->style['fontSize']);
        return $this;
    }

    public function add($song)
    {
        if (get_class($song) != 'Chordsify\Song')
            throw new Exception('Not a Chordsify\Song object');

        $this->songs[] = $song;
    }

    protected function lineY()
    {
        return ($this->line * $this->line_height)
            + $this->style['lineOffset']
            + $this->margin_top;
    }

    protected function nextColumn()
    {
        $this->column++;

        if ($this->column >= $this->columns) {
            $this->addPage();
        } else {
            $this->pdf->selectColumn($this->column);
            $this->line = 0;
        }
    }

    protected function nextLine()
    {
        $this->line++;

        if ($this->line >= $this->max_lines) {
            $this->nextColumn();
        }
    }

    protected function writePrefix($prefix)
    {
        $w = $this->pdf->GetStringWidth($prefix);
        $this->pdf->SetY($this->lineY());
        $this->pdf->SetX($this->pdf->GetX()+$this->style['indent']-$w);
        $this->pdf->Cell(
            $w,                    // width
            0,                     // height (auto)
            $prefix,               // text
            0,                     // border
            0,                     // cursor after
            'R',                   // align (prefix always align to the right of left margin)
            false,                 // fill
            '',                    // link
            0,                     // stretch
            true,                  // ignore min-height
            'L'                    // align cell to font baseline
        );
    }

    protected function writeLine($text)
    {
        $this->pdf->SetY($this->lineY());
        $this->pdf->SetX($this->pdf->GetX()+$this->style['indent']);
        $this->pdf->Cell(
            0,                     // width
            $this->line_height,    // height
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
        $this->nextLine();
    }

    protected function writeLyrics($song)
    {
        $this->setStyle('title');
        $this->writeLine($song->title);

        $sections = $song->sections();
        foreach ($sections as $i => $section) {
            $lyrics = $section->text(array('collapse'=>true, 'chords'=>false, 'sections'=>false));

            $lines = explode("\n", $lyrics);
            array_pop($lines);

            if ($i == count($sections)-1) {
                // Remove blank line at the end of the song
                array_pop($lines);
            }

            // Make sure the lines in the same section stays together in the same column
            // This shouldn't be needed in normal song sheet because each column should have enough space for the songs
            if ($this->line + count($lines) > $this->max_lines) {
                $this->nextColumn();
            }

            $this->setStyle('lyrics.'.$section->type);

            if ( ! empty($this->style['prefixText']))
            {
                $this->setStyle('lyrics.'.$section->type.'.prefix');
                $this->writePrefix($this->style['prefixText']);
                $this->setStyle('lyrics.'.$section->type);
            }

            foreach ($lines as $line) {
                // This is needed when copying PDF text to clipboard
                if ($line == '') {
                    $line = " ";
                }

                $this->writeLine($line);
            }
        }
    }

    // For debugging
    protected function drawGrid()
    {
        // Horizontal lines
        for ($i=0; $i < $this->max_lines; $i++) {
            $y = $i * $this->line_height + $this->margin_top;// + $this->styles['lineOffset'] /* root lineOffset*/;

            // Make darker line every tenth line
            if ($i % 10 == 9) {
                $this->pdf->SetDrawColor(0, 136, 140);
                //$this->pdf->SetDrawColor(140);
            } else {
                $this->pdf->SetDrawColor(102, 221, 238);
                //$this->pdf->SetDrawColor(221);
            }

            $this->pdf->Line(
                $this->gutter, $y,
                $this->page_w - $this->gutter, $y
            );
        }

        // Column boxes
        $this->pdf->SetDrawColor(238, 102, 102);
        for ($i=0; $i < $this->columns; $i++) {
            $x = ($i * Config::$pdf_column_width) + ($this->gutter * ($i * 2 + 1));
            $this->pdf->Rect(
                $x, $this->margin_top,
                Config::$pdf_column_width, $this->max_lines * $this->line_height
            );
        }
    }

    protected static function patternArray($pattern, $digit, $base)
    {
        $arr = array_fill(0, $digit, 0);
        $index = $digit-1;

        while ($pattern > 0 and $index >= 0) {
            $arr[$index] = ($pattern % $base);
            $index--;
            $pattern = (int) ($pattern / $base);
        }

        return $arr;
    }

    protected function evaluatePattern($p, $columns, $song_lengths)
    {
        $col_lengths = array_fill(0, $columns, 0);

        // For each song, add song length to each column
        foreach ($p as $i => $col) {
            $col_lengths[$col] += $song_lengths[$i];
        }

        // Check feasibility of pattern
        for ($col = 0; $col < $columns; $col++) {
            if ($col_lengths[$col] > $this->max_lines+2) {
                return null;
            }
        }

        // Find standard deviation for column length
        $average = array_sum($song_lengths) / $columns;
        $variance = 0;
        for ($col = 0; $col < $columns; $col++) {
            $variance += pow($col_lengths[$col] - $average, 2);
        }

        $sd = sqrt($variance/$columns);

        // Calculate song order's entropy
        $entropy = 0;
        $last_col = $p[0];
        foreach ($p as $col) {
            $entropy += abs($col - $last_col);
            $last_col = $col;
        }

        if ($this->debug) {
            echo 'S.D. = '.$sd.', Entropy = '.$entropy;
        }
        return $sd+$entropy;
    }

    // Determine the layout
    protected function packSongs()
    {
        // Find all the song's length
        $song_lengths = array();

        foreach ($this->songs as $i => $song) {
            $lyrics = $song->text(array('collapse'=>true, 'chords'=>false, 'sections'=>false));
            $lines = explode("\n", $lyrics);

            // This song length include 1 line for title and 2 lines for space between songs
            $song_lengths[$i] = count($lines) + 1;
        }

        $total_length = array_sum($song_lengths);

        // Figure out minimum number of columns we need
        // Note: each column can hold 2 lines longer than maximum because bottom 2 lines is a space between songs
        $columns = ceil($total_length / ($this->max_lines + 2));

        if ($columns == 1) {
            // No packing need -- one column
            return array(array(
                'offset' => (int) (($this->max_lines - $total_length) / 2 + 1),
                'songs' => $this->songs,
            ));
        }

        // We will iterate through all possible column assignments
        // But we'll fix first song on the first column
        $best_pattern = null;
        $best_score = null;

        while ($best_pattern === null) {
            if ($this->debug) {
                echo '<h1>Fitting '.count($this->songs).' songs into '.$columns.' columns</h1>';
            }

            $packed = array_fill(0, $columns, array('songs'=>array(), 'length'=>0));

            for ($pattern = 0; $pattern < pow($columns, count($this->songs)-1); $pattern++) {
                $p = $this->patternArray($pattern, count($this->songs), $columns);

                if ($this->debug) {
                    echo '<strong>'.json_encode($p).'</strong> ';
                }

                $score = $this->evaluatePattern($p, $columns, $song_lengths);
                if ($score === null) {
                    if ($this->debug) {
                        echo 'Invalid pattern<br>';
                    }
                    continue; // Invalid pattern
                }

                if ($best_score === null or $score < $best_score) {
                    $best_score = $score;
                    $best_pattern = $p;
                    if ($this->debug) {
                        echo ' <em>BEST</em>';
                    }
                }

                if ($this->debug) {
                    echo '<br>';
                }
            }

            if ($best_pattern === null) {
                // Try adding more columns
                $columns++;
            }
        }

        foreach ($best_pattern as $i => $col) {
            $packed[$col]['songs'][] = $this->songs[$i];
            $packed[$col]['length'] += $song_lengths[$i];
        }

        for ($col = 0; $col < $columns; $col++) {
            $packed[$col]['offset'] = (int) (($this->max_lines - $packed[$col]['length']) / 2 + 1);
        }

        return $packed;
    }

    protected function generate()
    {
        $print_songs = $this->packSongs();
        $columns = count($print_songs);

        // Calculate auto copies
        // 2 copies for results with 1-2 columns
        if ($this->copies == 'auto') {
            $this->copies = $columns < 3 ? 2 : 1;
        }

        // Duplicate columns for single column result
        if ($columns == 1 and $this->copies > 1) {
            while (count($print_songs) < $this->copies) {
                $print_songs[] = $print_songs[0];
            }
        }

        $this->addPage();
        foreach ($print_songs as $col => $col_info) {
            if ($col > 0) {
                $this->nextColumn();
            }

            $this->line = $col_info['offset'];

            for ($i = 0; $i < count($col_info['songs']); $i++) {
                $this->writeLyrics($col_info['songs'][$i]);

                if ($i < count($col_info['songs'])-1) {
                    // Space between songs
                    $this->writeLine(' ');
                    $this->writeLine(' ');
                }
            }
        }

        // Check if it ends on a new blank page, delete this blank page
        if ($this->line == 0) {
            $this->pdf->deletePage($this->pdf->PageNo());
        }

        // Make copies of the page
        if ($columns > 1 and $this->copies > 1) {
            $last_page = $this->pdf->PageNo();

            for ($i = 1; $i < $this->copies; $i++) {
                for ($p = 1; $p <= $last_page; $p++) {
                    $this->pdf->copyPage($p);
                }
            }
        }
    }

    public function pdf()
    {
        return $this->pdf;
    }

    public function pdfOutput($dest = 'I', $filename = 'songsheet.pdf')
    {
        $this->generate();
        return $this->pdf->Output($filename, $dest);
    }

    public function countPages()
    {
        $print_songs = $this->packSongs();
        return ceil(count($print_songs)/$this->columns);
    }
}
