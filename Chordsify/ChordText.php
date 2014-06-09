<?php
namespace Chordsify;

class ChordText extends Text
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
		return htmlspecialchars($this->content);
	}
}
