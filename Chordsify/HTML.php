<?php
namespace Chordsify;

class HTML
{
	public static function tag_open($name, array $attr = NULL)
	{
		$attrs = '';
		if ($attr)
		{
			foreach ($attr as $key => $value)
			{
				if ($value === '' or $value === NULL)
					continue;
				
				$attrs .= " $key=\"$value\"";
			}
		}

		return "<$name$attrs>";
	}

	public static function tag_close($name)
	{
		return "</$name>";
	}

	public static function tag($name, $content = '', array $attr = NULL)
	{
		return self::tag_open($name, $attr).$content.self::tag_close($name);
	}
}
