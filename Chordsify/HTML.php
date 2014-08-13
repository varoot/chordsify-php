<?php
namespace Chordsify;

class HTML
{
    public static function tagOpen($name, array $attr = null)
    {
        $attrs = '';
        if ($attr) {
            foreach ($attr as $key => $value) {
                if ($value === '' or $value === null)
                    continue;

                $attrs .= " $key=\"$value\"";
            }
        }

        return "<$name$attrs>";
    }

    public static function tagClose($name)
    {
        return "</$name>";
    }

    public static function tag($name, $content = '', array $attr = null)
    {
        return self::tagOpen($name, $attr).$content.self::tagClose($name);
    }
}
