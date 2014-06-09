<?php
namespace Chordsify;

class Lyrics extends Text
{
	public $content = '';

	public function parse($raw = '', array $options = NULL)
	{
		$this->content = $raw;
		return $this;
	}

	public function text(array $options = NULL)
	{
		return $this->content;
	}

	public function html(array $options = NULL)
	{
		if ($this->content === '')
			// A space is needed for chords to be on top
			return ' ';

		// Styling text
		$content = str_replace('\'', '’', $this->content);

		return Config::tag('lyrics', $content);
	}
}
