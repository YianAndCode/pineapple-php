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

    public function nextTokenIs(int $tokenType): Token
    {
        $nextToken = $this->getNextToken();
        if ($tokenType != $nextToken->tokenType) {
            $err = sprintf(
                "NextTokenIs(): syntax error near '%s', expected token: {%s} but got {%s}.",
                $nextToken->lineNum,
                TokenNameMap[$tokenType],
                TokenNameMap[$nextToken->tokenType]
            );
            throw new \Exception($err);
        }

        return $nextToken;
    }

    public function lookAheadAndSkip(int $expectedType)
    {
        $nowLineNum = $this->lineNum;
        $nextToken = $this->getNextToken();
        // not expected type, reverse cursor
        if ($nextToken->tokenType != $expectedType) {
            $this->lineNum          = $nowLineNum;
            $this->nextTokenLineNum = $nextToken->lineNum;
            $this->nextTokenType    = $nextToken->tokenType;
            $this->nextToken        = $nextToken->token;
        }
    }

    public function lookAhead(): int
    {
        if ($this->nextTokenLineNum > 0) {
            return $this->nextTokenType;
        }

        $nowLineNum = $this->lineNum;
        $nextToken = $this->getNextToken();
        $this->lineNum          = $nowLineNum;
        $this->nextTokenLineNum = $nextToken->lineNum;
        $this->nextTokenType    = $nextToken->tokenType;
        $this->nextToken        = $nextToken->token;
        return $nextToken->tokenType;
    }

    private function nextSourceCodeIs(string $s): bool
    {
        $prefix = mb_substr($this->sourceCode, 0, mb_strlen($s));
        return $prefix === $s;
    }

    private function skipSourceCode(int $n)
    {
        $this->sourceCode = mb_substr($this->sourceCode, $n);
    }

    private function isIgnored(): bool
    {
        $isIgnored = false;

        $isNewLine = function (string $c): bool {
            return $c == "\r" || $c == "\n";
        };

        $isWhiteSpace = function (string $c): bool {
            switch ($c) {
                case "\t":
                case "\n":
                case "\v":
                case "\f":
                case "\r":
                case " ":
                    return true;
            }
            return false;
        };

        while (mb_strlen($this->sourceCode) > 0) {
            if ($this->nextSourceCodeIs("\r\n") || $this->nextSourceCodeIs("\n\r")) {
                $this->skipSourceCode(2);
                $this->lineNum++;
                $isIgnored = true;
            } elseif ($isNewLine($this->sourceCode[0])) {
                $this->skipSourceCode(1);
                $this->lineNum++;
                $isIgnored = true;
            } elseif ($isWhiteSpace($this->sourceCode[0])) {
                $this->skipSourceCode(1);
                $isIgnored = true;
            } else {
                break;
            }
        }

        return $isIgnored;
    }

    private function scan(string $regStr): string
    {
        if (preg_match($regStr, $this->sourceCode, $token) && $token[0] != "") {
            $this->skipSourceCode(mb_strlen($token[0]));
            return $token[0];
        }

        throw new \Exception('Unreachable!');
    }

    private function scanBeforeToken(string $token): string
    {
        $s = explode($token, $this->sourceCode);
        if (count($s) < 2) {
            throw new \Exception('Unreachable!');
        }

        $this->skipSourceCode(mb_strlen($s[0]));

        return $s[0];
    }

    private function scanName(): string
    {
        return $this->scan(RegexName);
    }

    public function getNextToken(): Token
    {
        if ($this->nextTokenLineNum > 0) {
            $token = new Token;
            $token->lineNum   = $this->nextTokenLineNum;
            $token->tokenType = $this->nextTokenType;
            $token->token     = $this->nextToken;

            $this->lineNum          = $this->nextTokenLineNum;
            $this->nextTokenLineNum = 0;

            return $token;
        }

        return $this->matchToken();
    }

    public function matchToken(): Token
    {
        if ($this->isIgnored()) {
            return new Token($this->lineNum, TOKEN_IGNORED, TokenNameMap[TOKEN_IGNORED]);
        }
        if (mb_strlen($this->sourceCode) == 0) {
            return new Token($this->lineNum, TOKEN_EOF, TokenNameMap[TOKEN_EOF]);
        }
        switch ($this->sourceCode[0]) {
            case '$':
                $this->skipSourceCode(1);
                return new Token($this->lineNum, TOKEN_VAR_PREFIX, TokenNameMap[TOKEN_VAR_PREFIX]);
            case '(':
                $this->skipSourceCode(1);
                return new Token($this->lineNum, TOKEN_LEFT_PAREN, TokenNameMap[TOKEN_LEFT_PAREN]);
            case ')':
                $this->skipSourceCode(1);
                return new Token($this->lineNum, TOKEN_RIGHT_PAREN, TokenNameMap[TOKEN_RIGHT_PAREN]);
            case '=':
                $this->skipSourceCode(1);
                return new Token($this->lineNum, TOKEN_EQUAL, TokenNameMap[TOKEN_EQUAL]);
            case '"':
                if ($this->nextSourceCodeIs("\"\"")) {
                    $this->skipSourceCode(2);
                    return new Token($this->lineNum, TOKEN_DUOQUOTE, TokenNameMap[TOKEN_DUOQUOTE]);
                }
                $this->skipSourceCode(1);
                return new Token($this->lineNum, TOKEN_QUOTE, TokenNameMap[TOKEN_QUOTE]);
        }
        if ($this->sourceCode[0] == '_' || $this->isLetter($this->sourceCode[0])) {
            $token = $this->scanName();
            if (isset(Keywords[$token])) {
                return new Token($this->lineNum, Keywords[$token], $token);
            } else {
                return new Token($this->lineNum, TOKEN_NAME, $token);
            }
        }

        $err = sprintf("MatchToken(): unexpected symbol near '%s'.", $this->sourceCode[0]);
        throw new \Exception($err);
    }

    private function isLetter(string $c): bool
    {
        return $c >= 'a' && $c <= 'z' || $c >= 'A' && $c <= 'Z';
    }
}
