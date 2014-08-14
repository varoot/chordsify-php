<?php
namespace Chordsify;

use Symfony\Component\Yaml\Yaml;

class Config
{
    public static $sections = array(
        'intro', 'verse', 'prechorus', 'chorus', 'bridge', 'tag'
    );
    public static $chars = array('flat'=>'♭', 'sharp'=>'♯');

    // PDF for SongSheet
    public static $font_dir = '../fonts/';
    public static $pdf_columns = 2;
    public static $pdf_column_width = 230;

    /* For auto: 2 copies for 1 and 2 columns, 1 for 3+ */
    public static $pdf_copies = 'auto';

    public static $pdf_margin = 36;
    public static $pdf_size = 'Letter'; // Default size for PDF
    public static $pdf_style = 'center';

    // HTML
    public static $classes = array(
        'chord'       => 'chordsify-chord',
        'chordAnchor' => 'chordsify-chord-anchor',
        'chordRoot'   => 'chordsify-chord-inner',
        'gap'         => 'chordsify-gap',
        'gapDash'     => 'chordsify-gap-dash',
        'line'        => 'chordsify-line',
        'lyrics'      => 'chordsify-lyrics',
        'noChords'    => 'chordsify-no-chords',
        'paragraph'   => 'chordsify-paragraph',
        'section'     => 'chordsify-section',
        'song'        => 'chordsify',
        'word'        => 'chordsify-word',
    );
    public static $data_attr = array(
        'sectionType'  => 'data-section-type',
        'sectionNum'   => 'data-section-num',
        'chord'        => 'data-chord',
        'chordRel'     => 'data-chord-rel',
        'originalKey'  => 'data-original-key',
        'transposeKey' => 'data-transpose-to',
    );
    public static $elements = array(
        'chord'       => 'sup',
        'chordAnchor' => 'span',
        'chordRoot'   => 'span',
        'line'        => 'div',
        'lyrics'      => 'span',
        'paragraph'   => 'div',
        'section'     => 'div',
        'song'        => 'div',
        'word'        => 'span',
    );

    public static function tag($element, $content, array $extra_attr = null)
    {
        if ( ! is_array($extra_attr)) {
            $extra_attr = array();
        }
        $extra_attr['class'] = @self::$classes[$element];

        return HTML::tag(self::$elements[$element], $content, $extra_attr);
    }

    public static function tagOpen($element, array $extra_attr = null)
    {
        if ( ! is_array($extra_attr)) {
            $extra_attr = array();
        }
        $extra_attr['class'] = @self::$classes[$element];

        return HTML::tagOpen(self::$elements[$element], $extra_attr);
    }

    public static function tagClose($element)
    {
        return HTML::tagClose(self::$elements[$element]);
    }

    public static function loadStyle($style_name)
    {
        $all_styles = Yaml::parse(__DIR__.'/styles.yaml');
        if ( ! array_key_exists($style_name, $all_styles))
            return $all_styles['default'];
        return array_merge($all_styles['default'], $all_styles[$style_name]);
    }
}
