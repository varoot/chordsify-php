<?php
namespace Chordsify;

class SongSheet
{
	public $songs = array();
	public $pdf;
	public $debug = TRUE;

	protected $page_w;          // Page width
	protected $page_h;          // Page height
	protected $margin_top;      // Top margin
	protected $gutter;          // Column gutter
	protected $columns;         // Number of columns
	protected $max_lines;       // Number of lines per column
	protected $fonts = array(); // Fonts used

	protected $column;          // Current column
	protected $line;            // Current line to draw

	public function add_page()
	{
		$this->pdf->AddPage();

		if ($this->debug)
		{
			$this->draw_grid();
		}

		$this->pdf->selectColumn(0);
		$this->column = 0;
		$this->line = 0;
		return $this;
	}

	// Calculate column array
	protected function calculate_columns($columns)
	{
		$page_col_width = $this->page_w / $columns;
		$gutter = ($page_col_width - Config::$pdf_column_width) / 2;
		$this->gutter = $gutter;

		$cols = array();

		for ($i = 0; $i < $columns; $i++)
		{
			$cols[] = array(
				'w' => Config::$pdf_column_width,
				's' => ($i == $columns-1) ? $gutter : $gutter * 2,
				'y' => $this->margin_top,
			);
		}
		
		return $cols;
	}

	protected function set_font($font, $size)
	{
		// Check if font is loaded
		if (array_key_exists($font, $this->fonts))
		{
			$font = $this->fonts[$font];
		}
		else
		{
			// Load font if exists
			if (is_file(Config::$font_dir.$font))
			{
				$this->fonts[$font] = $this->pdf->addTTFfont(Config::$font_dir.$font);
				$font = $this->fonts[$font];
			}
		}

		$this->pdf->SetFont($font, '', $size);

		return $this;
	}

	protected function set_font_for($type, $subtype = '')
	{
		if (array_key_exists($type.'.'.$subtype, Config::$pdf_fonts))
		{
			$font = Config::$pdf_fonts[$type.'.'.$subtype];
		}
		else
		{
			$font = Config::$pdf_fonts[$type];
		}

		if (array_key_exists($type.'.'.$subtype, Config::$pdf_text_sizes))
		{
			$size = Config::$pdf_text_sizes[$type.'.'.$subtype];
		}
		else
		{
			$size = Config::$pdf_text_sizes[$type];
		}

		$this->set_font($font, $size);
		return $this;
	}

	function __construct($size = 'Letter', $columns = NULL)
	{
		if (empty($columns))
		{
			$columns = Config::$pdf_columns;
		}

		// Initialize PDF
		$pdf = new \TCPDF('P', 'pt' /* unit */, $size, true, 'UTF-8', false);

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
		$height = $this->page_h - (2 * Config::$pdf_margin);
		$this->max_lines = (int) ($height / Config::$pdf_line_height);
		$this->margin_top = ($this->page_h - ($this->max_lines * Config::$pdf_line_height)) / 2;

		// Set up page
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->setPageOrientation('P', false /* No auto page-break */, $this->margin_top - Config::$pdf_line_offset);
		$cols = $this->calculate_columns($columns);

		// Set up columns
		$this->columns = $columns;
		$pdf->setColumnsArray($cols);
		$pdf->SetMargins($this->gutter, $this->margin_top);

		// Set up fonts
		$pdf->setFontSubsetting(false);

		$this->pdf = $pdf;
	}

	public function add($song)
	{
		if (get_class($song) != 'Chordsify\Song')
			throw new Exception('Not a Chordsify\Song object');

		$this->songs[] = $song;
	}

	protected function line_y()
	{
		return ($this->line * Config::$pdf_line_height)
			+ Config::$pdf_line_offset
			+ $this->margin_top;
	}

	protected function next_column()
	{
		$this->column++;

		if ($this->column >= $this->columns)
		{
			$this->add_page();
		}
		else
		{
			$this->pdf->selectColumn($this->column);
			$this->line = 0;
		}
	}

	protected function next_line()
	{
		$this->line++;
		
		if ($this->line >= $this->max_lines)
		{
			$this->next_column();
		}
	}

	protected function write_line($text)
	{
		$this->pdf->SetY($this->line_y());
		$this->pdf->Cell(
			0,                        // width
			Config::$pdf_line_height, // height
			$text,                    // text,
			0,                        // border
			0,                        // cursor after
			'C',                      // align
			false,                    // fill
			'',                       // link
			1,                        // stretch
			true,                     // ignore min-height
			'L'                       // align cell to font baseline
		);
		$this->next_line();
	}

	protected function write_lyrics($song)
	{
		$this->set_font_for('title');
		$this->write_line($song->title);
		foreach ($song->sections() as $section)
		{
			$lyrics = $section->text(array('collapse'=>true, 'chords'=>false, 'sections'=>false));
			$this->set_font_for('lyrics', $section->type);
			
			$lines = explode("\n", $lyrics);
			array_pop($lines);

			if ($this->line + count($lines) >= $this->max_lines)
			{
				$this->next_column();
			}

			foreach ($lines as $line)
			{
				if ($line == '')
				{
					$line = " ";
				}

				$this->write_line($line);
			}
		}
	}

	// For debugging
	protected function draw_grid()
	{
		// Horizontal lines
		for ($i=0; $i < $this->max_lines; $i++)
		{
			$y = $i * Config::$pdf_line_height + $this->margin_top + Config::$pdf_line_offset;

			if ($i % 10 == 9)
			{
				$this->pdf->SetDrawColor(0, 136, 140);
				//$this->pdf->SetDrawColor(140);
			}
			else
			{
				$this->pdf->SetDrawColor(102, 221, 238);
				//$this->pdf->SetDrawColor(221);
			}

			$this->pdf->Line(
				$this->gutter, $y,
				$this->page_w - $this->gutter, $y
			);
		}

		// Vertical lines
		$this->pdf->SetDrawColor(238, 102, 102);
		for ($i=0; $i < $this->columns; $i++)
		{
			$x = ($i * Config::$pdf_column_width) + ($this->gutter * ($i * 2 + 1));
			$this->pdf->Rect(
				$x, $this->margin_top,
				Config::$pdf_column_width, $this->max_lines * Config::$pdf_line_height
			);
		}
	}

	protected static function pattern_array($pattern, $digit, $base)
	{
		$arr = array_fill(0, $digit, 0);
		$index = $digit-1;

		while ($pattern > 0 and $index >= 0)
		{
			$arr[$index] = ($pattern % $base);
			$index--;
			$pattern = (int) ($pattern / $base);
		}

		return $arr;
	}

	protected function evaluate_pattern($p, $columns, $song_lengths)
	{
		$col_lengths = array_fill(0, $columns, 0);

		// For each song, add song length to each column
		foreach ($p as $i => $col)
		{
			$col_lengths[$col] += $song_lengths[$i];
		}

		// Check feasibility of pattern
		for ($col = 0; $col < $columns; $col++)
		{
			if ($col_lengths[$col] > $this->max_lines+2)
			{
				return NULL;
			}
		}

		// Find standard deviation for column length
		$average = array_sum($song_lengths) / $columns;
		$variance = 0;
		for ($col = 0; $col < $columns; $col++)
		{
			$variance += pow($col_lengths[$col] - $average, 2);
		}

		$sd = sqrt($variance/$columns);

		// Calculate song order's entropy
		$entropy = 0;
		$last_col = $p[0];
		foreach ($p as $col)
		{
			$entropy += abs($col - $last_col);
			$last_col = $col;
		}

		if ($this->debug)
		{
			echo 'S.D. = '.$sd.', Entropy = '.$entropy;
		}
		return $sd+$entropy;
	}

	// Determine the layout
	protected function pack_songs()
	{
		// Find all the song's length
		$song_lengths = array();

		foreach ($this->songs as $i => $song)
		{
			$lyrics = $song->text(array('collapse'=>true, 'chords'=>false, 'sections'=>false));
			$lines = explode("\n", $lyrics);

			// This song length include 1 line for title and 2 lines for space between songs
			$song_lengths[$i] = count($lines) + 1;
		}

		$total_length = array_sum($song_lengths);

		// Figure out minimum number of columns we need
		// Note: each column can hold 2 lines longer than maximum because bottom 2 lines is a space between songs
		$columns = ceil($total_length / ($this->max_lines + 2));

		if ($columns == 1)
		{
			// No packing need -- one column
			return array(array(
				'offset' => (int) (($this->max_lines - $total_length) / 2 + 1),
				'songs' => $this->songs,
			));
		}

		// We will iterate through all possible column assignments
		// But we'll fix first song on the first column
		$best_pattern = NULL;
		$best_score = NULL;

		while ($best_pattern === NULL)
		{
			if ($this->debug)
			{
				echo '<h1>Fitting '.count($this->songs).' songs into '.$columns.' columns</h1>';
			}

			$packed = array_fill(0, $columns, array('songs'=>array(), 'length'=>0));

			for ($pattern = 0; $pattern < pow($columns, count($this->songs)-1); $pattern++)
			{
				$p = $this->pattern_array($pattern, count($this->songs), $columns);

				if ($this->debug)
				{
					echo '<strong>'.json_encode($p).'</strong> ';
				}

				$score = $this->evaluate_pattern($p, $columns, $song_lengths);
				if ($score === NULL)
				{
					if ($this->debug)
					{
						echo 'Invalid pattern<br>';
					}
					continue; // Invalid pattern
				}

				if ($best_score === NULL or $score < $best_score)
				{
					$best_score = $score;
					$best_pattern = $p;
					if ($this->debug)
					{
						echo ' <em>BEST</em>';
					}
				}

				if ($this->debug)
				{
					echo '<br>';
				}
			}

			if ($best_pattern === NULL)
			{
				// Try adding more columns
				$columns++;
			}
		}

		foreach ($best_pattern as $i => $col)
		{
			$packed[$col]['songs'][] = $this->songs[$i];
			$packed[$col]['length'] += $song_lengths[$i];
		}

		for ($col = 0; $col < $columns; $col++)
		{
			$packed[$col]['offset'] = (int) (($this->max_lines - $packed[$col]['length']) / 2 + 1);
		}

		return $packed;
	}

	protected function generate()
	{
		$print_songs = $this->pack_songs();

		$this->add_page();
		$this->line = 10;

		foreach ($print_songs as $col => $col_info)
		{
			if ($col > 0)
			{
				$this->next_column();
			}
			
			$this->line = $col_info['offset'];
			
			foreach ($col_info['songs'] as $song)
			{
				$this->write_lyrics($song);
				$this->write_line(' ');
			}
		}
	}

	public function pdf()
	{
		return $this->pdf;
	}

	public function pdf_output($dest = 'I')
	{
		$this->generate();
		return $this->pdf->Output('songsheet.pdf', $dest);
	}
}
