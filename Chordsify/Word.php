<?php
namespace Chordsify;

class Word extends Text
{
	public function parse($raw = '', array $options = NULL)
	{
		$data = preg_split('/\[([^\]]*)\]/', $raw, NULL, PREG_SPLIT_DELIM_CAPTURE);

		for ($i=0; $i < count($data); $i+=2)
		{
			if ($i==0 and $data[$i] == '')
				continue;

			$options = array('song'=>$this->song);

			if ($i > 0)
			{
				$options['chord'] = $data[$i-1];
			}

			$this->children[] = new Chunk($data[$i], $options);
		}

		return $this;
	}

	public function text(array $options = NULL)
	{
		$output = parent::text($options);

		if (isset($options['chords']) and ! $options['chords'])
		{
			// Remove spaces that are only there to separate chords
			return ltrim($output);
		}

		return $output;
	}

	public function html_before(array $options = NULL)
	{
		return HTML::tag_open(Config::$elements['word'], array('class'=>Config::$classes['word']));
	}

	public function html_after(array $options = NULL)
	{
		return HTML::tag_close(Config::$elements['word']);
	}
}
