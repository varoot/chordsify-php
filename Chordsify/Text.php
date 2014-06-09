<?php
namespace Chordsify;

abstract class Text {
	protected $children = array();
	protected $song;

	abstract public function parse($raw = '', array $options = NULL);

	function __construct($raw = '', array $options = NULL) {
		if (isset($options['song']))
		{
			$this->song = $options['song'];
		}
		
		$this->parse($raw, $options);
	}

	protected function text_before(array $options = NULL)
	{
		return '';
	}
	
	protected function text_after(array $options = NULL)
	{
		return '';
	}
	
	protected function html_before(array $options = NULL)
	{
		return '';
	}
	
	protected function html_after(array $options = NULL)
	{
		return '';
	}
	
	public function text(array $options = NULL)
	{
		$output = $this->text_before($options);

		foreach ($this->children as $child)
		{
			$output .= $child->text($options);
		}

		$output .= $this->text_after($options);

		return $output;
	}

	public function html(array $options = NULL)
	{
		$output = $this->html_before($options);

		foreach ($this->children as $child)
		{
			$output .= $child->html($options);
		}

		$output .= $this->html_after($options);

		return $output;
	}

	public function transpose($target_key)
	{
		foreach ($this->children as $child)
		{
			$child->transpose($target_key);
		}
		return $this;
	}

	public function __toString()
	{
		return $this->text();
	}
}
