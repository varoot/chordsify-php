<?php
namespace Chordsify;

class Config
{
	public static $sections = array('intro', 'verse', 'prechorus', 'chorus', 'bridge', 'tag');
	public static $chars = array('flat'=>'♭', 'sharp'=>'♯');
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

	public static function tag($element, $content, array $extra_attr = NULL)
	{
		if ( ! is_array($extra_attr))
		{
			$extra_attr = array();
		}
		$extra_attr['class'] = @self::$classes[$element];

		return HTML::tag(self::$elements[$element], $content, $extra_attr);
	}

	public static function tag_open($element, array $extra_attr = NULL)
	{
		if ( ! is_array($extra_attr))
		{
			$extra_attr = array();
		}
		$extra_attr['class'] = @self::$classes[$element];

		return HTML::tag_open(self::$elements[$element], $extra_attr);
	}

	public static function tag_close($element)
	{
		return HTML::tag_close(self::$elements[$element]);
	}
}
