<?php

namespace AppBundle\Doctrine\Functions;


use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

class Hextoraw extends FunctionNode
{
    public $field;

    /**
     * @param SqlWalker $sqlWalker
     * @return string
     */
    public function getSql(SqlWalker $sqlWalker)
    {
        return 'HEXTORAW('.$this->field->dispatch($sqlWalker).')';
    }

    /**
     * @param Parser $parser
     */
    public function parse(Parser $parser)
    {
        $parser->match(Lexer::T_IDENTIFIER);
        $parser->match(Lexer::T_OPEN_PARENTHESIS);
        $this->field = $parser->StringPrimary();
        $parser->match(Lexer::T_CLOSE_PARENTHESIS);
    }

    /**
     * Get expression value string.
     *
     * @param string|Node $expression
     * @param SqlWalker   $sqlWalker
     * @return string
     */
    protected function getExpressionValue($expression, SqlWalker $sqlWalker)
    {
        if ($expression instanceof Node) {
            $expression = $expression->dispatch($sqlWalker);
        }
        return $expression;
    }
}
