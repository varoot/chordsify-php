<?php
namespace Chordsify;

class Lyrics extends Unit
{
	public $content = '';

	public function parse($raw = '', array $options = [])
	{
		$this->content = $raw;
		return $this;
	}

    public function write($writer)
    {
        return $writer->lyrics($this);
    }

	public function formatted_content()
	{
		$content = $this->content;
		$content = str_replace('\'', '’', $content);

		return $content;
	}
}
