<?php
namespace Chordsify;

class Lyrics extends Text
{
	public $content = '';

	public function parse($raw = '', array $options = null)
	{
		$this->content = $raw;
		return $this;
	}

	protected function formatted_content()
	{
		$content = $this->content;
		$content = str_replace('\'', '’', $content);

		return $content;
	}

	public function text(array $options = null)
	{
		if (isset($options['formatted']) and $options['formatted'] == false)
		{
			return $this->content;
		}

		return $this->formatted_content();
	}

	public function html(array $options = null)
	{
		if ($this->content === '')
			// A space is needed for chords to be on top
			return ' ';

		// Styling text
		if (isset($options['formatted']) and $options['formatted'] == false)
		{
			$content = $this->content;
		}
		else
		{
			$content = $this->formatted_content();
		}

		return Config::tag('lyrics', $content);
	}
}
