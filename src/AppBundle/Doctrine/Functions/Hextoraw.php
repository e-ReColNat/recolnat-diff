<?php

namespace AppBundle\Doctrine\Functions;


use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class Hextoraw extends FunctionNode
{
    public $field;

    public function getSql(SqlWalker $sqlWalker) {
        $query = "HEXTORAW('" . $this->field->dispatch($sqlWalker)."')" ;
        return $query;
    }

    public function parse(Parser $parser) {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->field = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }
}