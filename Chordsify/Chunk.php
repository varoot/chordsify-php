<?php
namespace Chordsify;

class Chunk extends Text
{
	public function parse($raw = '', array $options = NULL)
	{
		$this->children['lyrics'] = new Lyrics($raw, array('song'=>$this->song));

		if ( ! empty($options['chord']))
		{
			$this->children['chord'] = new Chord($options['chord'], array('song'=>$this->song));
		}

		return $this;
	}

	public function text(array $options = NULL)
	{
		$output = $this->children['lyrics']->text($options);

		if (isset($options['chords']) and ! $options['chords'])
		{
			return $output;
		}

		if (@$this->children['chord'])
		{
			$output = $this->children['chord']->text($options).$output;
		}

		return $output;
	}

	public function html(array $options = NULL)
	{
		$output = $this->children['lyrics']->html($options);

		if (isset($options['chords']) and ! $options['chords'])
		{
			return $output;
		}

		if (@$this->children['chord'])
		{
			$output = $this->children['chord']->html($options).$output;
		}
		
		return $output;
	}
}
