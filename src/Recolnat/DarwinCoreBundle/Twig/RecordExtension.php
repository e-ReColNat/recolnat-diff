<?php
namespace Recolnat\DarwinCoreBundle\Twig;


class RecordExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            'getDiff' => new \Twig_Function_Method($this, 'getDiff')
        );
    }
    public function getDiff($text1, $text2) 
    {
        $opcodes = \FineDiff::getDiffOpcodes($text1, $text2);
        $result =  \FineDiff::renderDiffToHTMLFromOpcodes($text1, $opcodes);
        return $result ;
    }
    public function getName()
    {
        return 'darwincoreExtension';
    }
}