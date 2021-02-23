<?php

namespace Pineapple;

class Parser
{
    private $lexer;

    public function __construct(string $sourceCode)
    {
        $this->lexer = new Lexer($sourceCode);
    }
}
