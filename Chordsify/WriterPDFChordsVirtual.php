<?php
namespace Chordsify;

class WriterPDFChordsVirtual extends WriterPDFChords
{
    public function dash($x, $y, $len) {}
    public function writeText($x, $y, $text) {}
    public function writeCell($x, $y, $text) {}
}
