<?php

namespace Pineapple;

class Lexer
{
    private $sourceCode;
    private $lineNum;
    private $nextToken;
    private $nextTokenType;
    private $nextTokenLineNum;

    public function __construct(string $sourceCode)
    {
        $this->sourceCode         = $sourceCode;
        $this->lineNum            = 1; // start at line 1 in default.
        $this->nextToken          = "";
        $this->nextTokenType      = TOKEN_EOF;
        $this->nextTokenLineNum   = 0;
    }

    public function getLineNum()
    {
        return $this->lineNum;
    }

    public function nextTokenIs(int $tokenType)
    {
        //
    }

    public function lookAheadAndSkip(int $expectedType)
    {
        //
    }

    public function lookAhead()
    {
        //
    }

    private function nextSourceCodeIs(string $s): bool
    {
        //
    }

    private function skipSourceCode(int $n)
    {
        //
    }

    private function isIgnored(): bool
    {
        //
    }

    private function scan(): string
    {
        //
    }

    private function scanBeforeToken(string $token): string
    {
        //
    }

    public function getNextToken()
    {
        //
    }

    public function MatchToken()
    {
        //
    }

    private function isLetter($c): bool
    {
        //
    }
}
