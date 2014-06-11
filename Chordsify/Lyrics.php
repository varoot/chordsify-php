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

	protected function formatted_content()
	{
		$content = $this->content;
		$content = str_replace('\'', '’', $content);

		return $content;
	}

	public function text(array $options = NULL)
	{
		if (isset($options['formatted']) and $options['formatted'] == FALSE)
		{
			return $this->content;
		}

		return $this->formatted_content();
	}

	public function html(array $options = NULL)
	{
		if ($this->content === '')
			// A space is needed for chords to be on top
			return ' ';

		// Styling text
		if (isset($options['formatted']) and $options['formatted'] == FALSE)
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
