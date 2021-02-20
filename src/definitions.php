<?php

namespace Pineapple;

const TOKEN_EOF         = 0; // end-of-file
const TOKEN_VAR_PREFIX  = 1; // $
const TOKEN_LEFT_PAREN  = 2; // (
const TOKEN_RIGHT_PAREN = 3; // )
const TOKEN_EQUAL       = 4; // =
const TOKEN_QUOTE       = 5; // "
const TOKEN_DUOQUOTE    = 6; // ""
const TOKEN_NAME        = 7; // Name ::= [_A-Za-z][_0-9A-Za-z]*
const TOKEN_PRINT       = 8; // print
const TOKEN_IGNORED     = 9; // Ignored

const TokenNameMap = [
    TOKEN_EOF         => "EOF",
    TOKEN_VAR_PREFIX  => "$",
    TOKEN_LEFT_PAREN  => "(",
    TOKEN_RIGHT_PAREN => ")",
    TOKEN_EQUAL       => "=",
    TOKEN_QUOTE       => "\"",
    TOKEN_DUOQUOTE    => "\"\"",
    TOKEN_NAME        => "Name",
    TOKEN_PRINT       => "print",
    TOKEN_IGNORED     => "Ignored",
];

const Keywords = [
    "print" => TOKEN_PRINT,
];
