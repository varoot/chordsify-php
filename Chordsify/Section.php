<?php
namespace Chordsify;

class Section extends Text
{
    public $type = '';
    public $number = 0;

    public function parse($raw = '', array $options = null)
    {
        $data = array_filter(preg_split('/(\s*\n){2}/', $raw));

        foreach ($data as $p) {
            $this->children[] = new Paragraph(trim($p), array('song'=>$this->song));
        }

        return $this;
    }

    public function __construct($raw = '', array $options = null) {
        $this->type = (string) $options['type'];
        $this->number = (int) $options['number'];
        parent::__construct($raw, $options);
    }

    protected function text_before(array $options = null)
    {
        if (empty($this->type) or (isset($options['sections']) and ! $options['sections']))
            return '';

        return '['.$this->type.($this->number > 0 ? ' '.$this->number : '')."]\n";
    }

    public function html_before(array $options = null)
    {
        return Config::tagOpen('section', array(
            Config::$data_attr['sectionType'] => $this->type,
            Config::$data_attr['sectionNum'] => $this->number > 0 ? $this->number : '',
        ));
    }

    public function html_after(array $options = null)
    {
        return Config::tagClose('section');
    }
}
