<?php
namespace Chordsify;

class Config
{
    public static $sections = [
        'intro', 'verse', 'prechorus', 'chorus', 'bridge', 'tag'
    ];
    public static $chars = ['b'=>'♭', '#'=>'♯'];

    // PDF for SongSheet
    public static $font_dir = '../fonts/';
    public static $pdfStyleSheet = 'styles.yaml';

    public static function loadStyle($style_name)
    {
        $all_styles = Yaml::parse(__DIR__.'/styles.yaml');
        if ( ! array_key_exists($style_name, $all_styles))
            return $all_styles['default'];
        return array_merge($all_styles['default'], $all_styles[$style_name]);
    }
}
