<?php
namespace Chordsify;

class Chunk extends Text
{
	public $chord;
	public $lyrics;

	public function parse($raw = '', array $options = NULL)
	{
		if ( ! empty($options['chord']))
		{
			$this->chord = new Chord($options['chord']);
		}

		$this->lyrics = new Lyrics($raw);
	}

	public function text()
	{
		return $this->chord.$this->lyrics;
	}
}
