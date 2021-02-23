<?php

namespace Pineapple;

class Parser
{
    private $lexer;

    public function __construct(string $sourceCode)
    {
        $this->lexer = new Lexer($sourceCode);
    }

    public function parse(): SourceCode
    {
        $sourceCode = $this->parseSourceCode();

        $this->lexer->nextTokenIs(TOKEN_EOF);

        return $sourceCode;
    }

    private function isSourceCodeEnd(int $token): bool
    {
        return $token == TOKEN_EOF;
    }

    // SourceCode ::= Statement+
    private function parseSourceCode(): SourceCode
    {
        $sourceCode = new SourceCode;
        $sourceCode->lineNum    = $this->lexer->getLineNum();
        $sourceCode->statements = $this->parseStatements();
        return $sourceCode;
    }

    private function parseStatements(): array
    {
        $statements = [];

        while (! $this->isSourceCodeEnd($this->lexer->lookAhead())) {
            $statements[] = $this->parseStatement();
        }

        return $statements;
    }

    // Statement ::= Print | Assignment
    private function parseStatement(): Statement
    {
        $this->lexer->lookAheadAndSkip(TOKEN_IGNORED);
        switch ($this->lexer->lookAhead()) {
            case TOKEN_PRINT:
                return $this->parsePrint();
            case TOKEN_VAR_PREFIX:
                return $this->parseAssignment();
            default:
                throw new \Exception('parseStatement(): unknown Statement.');
        }
    }

    // Print ::= "print" "(" Ignored Variable Ignored ")" Ignored
    private function parsePrint(): PrintStatement
    {
        $print = new PrintStatement;
        $print->lineNum = $this->lexer->getLineNum();

        $this->lexer->nextTokenIs(TOKEN_PRINT);
        $this->lexer->nextTokenIs(TOKEN_LEFT_PAREN);
        $this->lexer->lookAheadAndSkip(TOKEN_IGNORED);
        $print->variable = $this->parseVariable();
        $this->lexer->lookAheadAndSkip(TOKEN_IGNORED);
        $this->lexer->nextTokenIs(TOKEN_RIGHT_PAREN);
        $this->lexer->lookAheadAndSkip(TOKEN_IGNORED);
        return $print;
    }

    // Assignment  ::= Variable Ignored "=" Ignored String Ignored
    private function parseAssignment(): AssignmentStatement
    {
        $assignment = new AssignmentStatement;

        $assignment->lineNum  = $this->lexer->getLineNum();
        $assignment->variable = $this->parseVariable();

        $this->lexer->lookAheadAndSkip(TOKEN_IGNORED);
        $this->lexer->nextTokenIs(TOKEN_EQUAL);
        $this->lexer->lookAheadAndSkip(TOKEN_IGNORED);
        $assignment->string = $this->parseString();
        $this->lexer->lookAheadAndSkip(TOKEN_IGNORED);
        return $assignment;
    }

    // Name ::= [_A-Za-z][_0-9A-Za-z]*
    private function parseName(): string
    {
        $nextToken = $this->lexer->nextTokenIs(TOKEN_NAME);
        return $nextToken->token;
    }

    // String ::= '"' '"' Ignored | '"' StringCharacter '"' Ignored
    private function parseString(): string
    {
        $str = "";
        switch ($this->lexer->lookAhead()) {
            case TOKEN_DUOQUOTE:
                $this->lexer->nextTokenIs(TOKEN_DUOQUOTE);
                $this->lexer->lookAheadAndSkip(TOKEN_IGNORED);
                break;

            case TOKEN_QUOTE:
                $this->lexer->nextTokenIs(TOKEN_QUOTE);
                $str = $this->lexer->scanBeforeToken(TokenNameMap[TOKEN_QUOTE]);
                $this->lexer->nextTokenIs(TOKEN_QUOTE);
                $this->lexer->lookAheadAndSkip(TOKEN_IGNORED);
                break;

            default:
                throw new \Exception("parseString(): not a string.");
        }

        return $str;
    }

    // Variable ::= "$" Name Ignored
    private function parseVariable(): Variable
    {
        $variable = new Variable;

        $variable->lineNum = $this->lexer->getLineNum();
        $this->lexer->nextTokenIs(TOKEN_VAR_PREFIX);
        $variable->name = $this->parseName();
        $this->lexer->lookAheadAndSkip(TOKEN_IGNORED);

        return $variable;
    }
}
