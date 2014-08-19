<?php
namespace Chordsify;

class SongSheetFitter
{
    use PDFStyle;

    protected $options = [
        'chords'    => false,
        'collapse'  => 0,     // 0 = No collapse, 1 = Always collapse, 2+ = Collapse if saves n lines
        'formatted' => true,  // make curly quotes
        'style'     => 'left',
    ];

    public $debug = false;

    protected $pdf;
    protected $height;

    protected $metrics = [];

    public function __construct(\TCPDF $pdf, $height, array $options = [])
    {
        $this->pdf     = $pdf;
        $this->height  = (int) $height;
        $this->options = array_merge($this->options, $options);
        $this->loadStyleSheet($this->options['style']);
        $this->setStyle('');
    }

    protected function metrics(Song $song)
    {
        if ( ! array_key_exists($song->hash, $this->metrics)) {
            $m = ['song' => $song];
            $opt = $this->options;

            if ($this->options['chords']) {
                $writer = new WriterPDFChordsVirtual($this->pdf, $opt);
                $m['collapse'] = [ $song->write($writer) ];
            } else {
                $opt['collapse'] = 'all';
                $writer = new WriterPDFVirtual($this->pdf, $opt);
                $m['collapse'] = $song->write($writer);
            }

            $m['height'] = array_column($m['collapse'], 'height');

            $this->metrics[$song->hash] = $m;
        }

        return $this->metrics[$song->hash];
    }

    protected function accumulateHeights($songs)
    {
        $total = [ 0 ];
        foreach ($songs as $song) {
            $m = $this->metrics($song);
            $height = $m['height'];

            if (count($height) > count($total)) {
                for ($i = count($total); $i < count($height); $i++) {
                    $total[$i] = $total[0];
                }
            }

            for ($i = 0; $i < count($total); $i++) {
                $total[$i] += $height[$i >= count($height) ? 0 : $i] + $this->style['songSpace'];
            }
        }

        // Always return at least two values:
        //     $total[0] = no collapse (max height)
        //     $total[1] = maximum collapse (min height)
        if (count($total) == 1) {
            $total[1] = $total[0];
        }
        return $total;
    }

    protected function collapseArray($songs)
    {
        $output = [];
        foreach ($songs as $song) {
            $m = $this->metrics($song);
            $output[$song->hash] = count($m['height']);
        }
        return $output;
    }

    protected function heightOnCollapse($songs, $collapse) {
        $total = 0;
        foreach ($songs as $song) {
            $m = $this->metrics($song);
            $level = array_key_exists($song->hash, $collapse) ? $collapse[$song->hash] : 0;
            if ($level >= count($m['height'])) {
                $level = 0;
            }
            $total += $m['height'][$level] + $this->style['songSpace'];
        }
        return $total;
    }

    protected function songList($columnSongs, $collapse) {
        $songList = [];
        foreach ($columnSongs as $col => $songs) {
            $colData = [];
            $y = ($this->height - $this->heightOnCollapse($songs, $collapse) + $this->style['songSpace']) / 2;
            // Snap Y to lineHeight
            $y = (int) ($y / $this->style['lineHeight']) * $this->style['lineHeight'];
            foreach ($songs as $song) {
                $m = $this->metrics($song);
                $level = array_key_exists($song->hash, $collapse) ? $collapse[$song->hash] : 0;
                $colData[] = array_merge($m['collapse'][$level], ['y' => $y, 'song' => $song, 'collapse' => $level]);
                $y += $m['collapse'][$level]['height'] + $this->style['songSpace'];
            }
            $songList[$col] = $colData;
        }

        return $songList;
    }

    public function fit(array $songs)
    {
        // Recalculate all metrics
        $this->metrics = [];

        // Get song's dimensions (width & heights)
        // Calculate total length of all songs
        $totalHeights = $this->accumulateHeights($songs);

        // Figure out minimum number of columns we need
        // Note: each column can hold 2 lines longer than maximum because bottom 2 lines is a space between songs
        $columns = (int) ceil($totalHeights[1] / ($this->height + $this->style['songSpace']));

        if ($columns == 1) {
            // No packing need -- one column

            // Find maximum collapse level
            $collapseArray = $this->collapseArray($songs);
            $iter = new PatternIterator($collapseArray);
            foreach ($iter as $collapse) {
                $height = $this->heightOnCollapse($songs, $collapse);
                if ($height <= $this->height + $this->style['songSpace'])
                    break;
            }

            return $this->songList([ $songs ], $collapse);
        } elseif ($columns == 2) {

            // We will iterate through all possible column assignments
            // But we'll fix first song on the first column
            $patterns = [];

            while (count($patterns) == 0) {
                $possibleColumns = [];
                foreach ($songs as $i => $song) {
                    $possibleColumns[$song->hash] = ($i == 0) ? 1 : $columns;
                }

                $iter = new PatternIterator($possibleColumns, true);
                foreach ($iter as $key => $colPattern) {
                    if ($this->debug) {
                        echo "<strong>$key:</strong> ";
                    }

                    $eval = $this->evaluatePattern($songs, $columns, $colPattern);
                    if ( ! $eval) {
                        // Invalid pattern
                        continue;
                    }

                    $patterns[] = array_merge($eval, ['columns' => $colPattern]);

                    if ($this->debug) {
                        echo "\n";
                    }
                }

                $columns++;
            }

            $columns--;
        } else {
            // Best-fit first
            $bins = array_fill(0, $columns, [ 'space' => $this->height + $this->style['songSpace'], 'songs' => [] ]);
            $sortedSongs = $songs;
            $heights = [];
            $orders = [];
            foreach ($sortedSongs as $i => $song) {
                $orders[$song->hash] = $i;
                $m = $this->metrics($song);
                $heights[$song->hash] = $m['height'][count($m['height']) > 1 ? 1 : 0];
            }

            usort($sortedSongs, function($a, $b) use ($heights) {
                return $heights[$a->hash] - $heights[$b->hash];
            });

            foreach ($sortedSongs as $song) {
                $m = $this->metrics($song);
                $height = $heights[$song->hash] + $this->style['songSpace'];
                $found = false;
                foreach ($bins as &$bin) {
                    if ($bin['space'] > $height) {
                        $found = true;
                        $bin['songs'][] = $song->hash;
                        $bin['space'] -= $height;
                        break;
                    }
                }

                if ( ! $found) {
                    $bins[] = [ 'space' => $this->height + $this->style['songSpace'] - $height, 'songs' => [ $song->hash ] ];
                    $columns++;
                }
            }

            $colPattern = [];
            foreach ($bins as &$bin) {
                usort($bin['songs'], function($a, $b) use ($orders) { return $orders[$a] - $orders[$b]; });
                $colPattern[] = $bin['songs'];
            }

            $eval = $this->evaluatePattern($songs, $columns, $colPattern);
            $patterns[] = array_merge($eval, ['columns' => $colPattern]);
        }

        // Remove dominated patterns
        $patterns = Utils::removeDominated($patterns, ['sd'=>false, 'entropy'=>false]);

        // Pick the best one (lowest sd + entropy)
        $bestPattern = null;
        foreach ($patterns as $p) {
            if ($bestPattern === null) {
                $bestPattern = $p;
            } else {
                if ($bestPattern['sd']+$bestPattern['entropy'] > $p['sd'] + $p['entropy']) {
                    $bestPattern = $p;
                }
            }
        }

        $colSongList = [];
        foreach ($bestPattern['columns'] as $songHashes) {
            $colSongs = [];
            foreach ($songHashes as $hash) {
                $colSongs[] = $this->metrics[$hash]['song'];
            }
            $colSongList[] = $colSongs;
        }

        return $this->songList($colSongList, $bestPattern['collapse']);
    }

    // Invalid pattern returns null
    // Valid pattern returns an array
    //     - collapse:  best collapse pattern
    //     - sd:        standard deviation
    //     - entropy:   song order entropy
    protected function evaluatePattern($songs, $columns, $columnPattern)
    {
        // Check feasibility of pattern
        // 1. Check if all columns are used
        if (count($columnPattern) < $columns) {
            if ($this->debug) {
                echo "INVALID (not all columns used)\n";
            }
            return NULL;
        }

        // 2. Check if each column can fit on page
        // Assuming max collapse first
        $collapse = array_fill(0, count($songs), 1);
        $colSongList = array_fill(0, $columns, []); // [ column => songs ]
        $songColumns = []; // [ songhash => column ]
        foreach ($columnPattern as $col => $songHashes) {
            foreach ($songHashes as $hash) {
                $colSongList[$col][] = $this->metrics[$hash]['song'];
                $songColumns[$hash] = $col;
            }
            $height = $this->heightOnCollapse($colSongList[$col], $collapse);
            if ($height > $this->height + $this->style['songSpace']) {
                if ($this->debug) {
                    echo "INVALID (height exceed)\n";
                }
                return NULL;
            }
        }

        if ($this->debug) {
            echo "VALID ";
        }

        $bestSD = null;
        $bestCollapse = null;

        // Iterate through all possible collapse levels
        $collapseArray = $this->collapseArray($songs);
        $iter = new PatternIterator($collapseArray);
        foreach ($iter as $collapse) {
            $valid = true;
            $heights = [];
            foreach ($colSongList as $colSongs) {
                $height = $this->heightOnCollapse($colSongs, $collapse);
                if ($height > $this->height + $this->style['songSpace']) {
                    // Invalid collapse
                    $valid = false;
                    break;
                }
                $heights[] = $height;
            }

            if ( ! $valid) {
                continue;
            }

            // Find standard deviation for column length
            $sd = Utils::sd($heights) / $this->style['lineHeight'];
            if ($bestSD === null or $sd < $bestSD) {
                $bestSD = $sd;
                $bestCollapse = $collapse;
            }
        }

        // Calculate song order's entropy
        $entropy = 0;
        $lastCol = 0;
        foreach ($songs as $song) {
            $col = $songColumns[$song->hash];
            $entropy += abs($col - $lastCol);
            $lastCol = $col;
        }

        if ($this->debug) {
            echo $bestSD+$entropy." = ";
            echo $bestSD.' (S.D.) + '.$entropy.' (Entropy)';
        }

        return [ 'sd' => $bestSD, 'entropy' => $entropy, 'collapse' => $bestCollapse ];
    }

    public function pdf()
    {
        return $this->pdf;
    }
}
