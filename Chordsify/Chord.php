<?php
namespace Chordsify;

class Chord extends Text
{
	public $main_root;

	public function parse($raw = '', array $options = NULL)
	{
		$data = preg_split('/([A-G](?:#|b)?)/', $raw, NULL, PREG_SPLIT_DELIM_CAPTURE);
		
		for ($i = 0; $i < count($data); $i+=2)
		{
			if ($i == 0 and $data[$i] == '')
				continue;

			if ($i > 1)
			{
				$root = new ChordRoot($data[$i-1], array('song'=>$this->song));
				if ( ! isset($this->main_root))
				{
					$this->main_root = $root;
				}

				$this->children[] = $root;
			}

			if ($data[$i] != '')
			{
				$this->children[] = new ChordText($data[$i], array('song'=>$this->song));
			}
		}

		return $this;
	}

	public function text_before(array $options = NULL)
	{
		return '[';
	}

	public function text_after(array $options = NULL)
	{
		return ']';
	}

	public function html_before(array $options = NULL)
	{
		return Config::tag_open('chordAnchor').Config::tag_open('chord', array(
			Config::$data_attr['chord']=>Key::value($this->main_root->root)
		));
	}
	
	public function html_after(array $options = NULL)
	{
		return Config::tag_close('chord').Config::tag_close('chordAnchor');
	}
}
