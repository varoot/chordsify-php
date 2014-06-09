<?php
namespace Chordsify;

class Song extends Text
{
	protected $_original_key;

	public function parse($raw = '', array $options = NULL)
	{
		$data = preg_split('/^\s*\[\s*('.implode('|', Config::$sections).')\s*(\d*)\s*\]\s*$/m', $raw, NULL, PREG_SPLIT_DELIM_CAPTURE);
		
		for ($i=0; $i < count($data); $i+=3)
		{
			if ($i==0 and trim($data[$i]) == '')
			{
				// Skip empty section at the beginning
				continue;
			}

			$this->children[] = new Section($data[$i], array(
				'song' => $this,
				'type' => $i > 0 ? $data[$i-2] : NULL,
				'number' => $i > 0 ? $data[$i-1] : NULL,
			));
		}

		return $this;
	}

	public function html_before(array $options = NULL)
	{
		return Config::tag_open('song');
	}

	public function html_after(array $options = NULL)
	{
		return Config::tag_close('song');
	}

	public function original_key()
	{
		return $this->_original_key;
	}

	public function transpose($target_key)
	{
		$target_key = Key::value($target_key);
		return parent::transpose($target_key);
	}

	function __construct($raw = '', array $options = NULL)
	{
		if (isset($options['original_key']))
		{
			$o_key = $options['original_key'];
		}
		else
		{
			$o_key = NULL;
		}

		$this->_original_key = new Key($o_key);
		parent::__construct($raw, $options);
	}
}
