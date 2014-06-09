<?php
namespace Chordsify;

class Line extends Text
{
	public function parse($raw = '', array $options = NULL)
	{
		preg_match_all('/\S*(\[[^\]]\]\S*)*\s*/', trim($raw), $matches);
		
		foreach ($matches[0] as $word)
		{
			$this->children[] = new Word($word, array('song'=>$this->song));
		}

		return $this;
	}

	protected function text_after(array $options = NULL)
	{
		return "\n";
	}

	public function html_before(array $options = NULL)
	{
		return Config::tag_open('line');
	}

	public function html_after(array $options = NULL)
	{
		return Config::tag_close('line');
	}
}
