<?php

namespace Pineapple;

class Backend
{
    private $ast;

    private $globalVar;

    public function Execute(string $sourceCode)
    {
        $parser = new Parser($sourceCode);

        $this->ast = $parser->parse();
        $this->globalVar = [];

        $this->resolveAST();
    }

    private function resolveAST()
    {
        if (count($this->ast->statements) == 0) {
            throw new \Exception('resolveAST(): no code to execute, please check your input.');
        }

        foreach ($this->ast->statements as $statement) {
            $this->resolveStatement($statement);
        }
    }

    private function resolveStatement(Statement $statement)
    {
        if ($statement instanceof AssignmentStatement) {
            $this->resolveAssignment($statement);
        } else if ($statement instanceof PrintStatement) {
            $this->resolvePrint($statement);
        } else {
            throw new \Exception('resolveStatement(): undefined statement type.');
        }
    }

    private function resolveAssignment(AssignmentStatement $statement)
    {
        $varName = $statement->variable->name;
        if (empty($varName)) {
            throw new \Exception('resolveAssignment(): variable name can NOT be empty.');
        }
        $this->globalVar[$varName] = $statement->string;
    }

    private function resolvePrint(PrintStatement $statement)
    {
        $varName = $statement->variable->name;
        if (empty($varName)) {
            throw new \Exception('resolvePrint(): variable name can NOT be empty.');
        }
        if (! isset($this->globalVar[$varName])) {
            throw new \Exception(
                sprintf("resolvePrint(): variable '$%s'not found.", $varName)
            );
        }

        file_put_contents("php://stdout", $this->globalVar[$varName]);
    }
}
