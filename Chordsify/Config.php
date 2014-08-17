<?php
namespace Chordsify;

use Symfony\Component\Yaml\Yaml;

class Config
{
    public static $sections = [
        'intro', 'verse', 'prechorus', 'chorus', 'bridge', 'tag'
    ];
    public static $chars = ['b'=>'♭', '#'=>'♯'];

    // PDF for SongSheet
    public static $font_dir = '../fonts/';
    public static $pdf_columns = 2;

    /* Column width is fixed to help with consistency among A4 and Letter paper size */
    public static $pdf_column_width = 230;

    /* For auto: 2 copies for 1 and 2 columns, 1 for 3+ */
    public static $pdf_copies = 'auto';

    /* Note: margin is only used for top & bottom
       Left & right is calculated from column width */
    public static $pdf_margin = 36;
    public static $pdf_size = 'Letter'; // Default size for PDF
    public static $pdf_style = 'center';

    public static function loadStyle($style_name)
    {
        $all_styles = Yaml::parse(__DIR__.'/styles.yaml');
        if ( ! array_key_exists($style_name, $all_styles))
            return $all_styles['default'];
        return array_merge($all_styles['default'], $all_styles[$style_name]);
    }
}
