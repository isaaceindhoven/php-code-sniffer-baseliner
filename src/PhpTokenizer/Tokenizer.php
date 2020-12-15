<?php

declare(strict_types=1);

namespace ISAAC\CodeSnifferBaseliner\PhpTokenizer;

use function is_array;
use function substr_count;
use function token_get_all;

class Tokenizer
{
    public function tokenize(string $sourceCode): TokenizedSourceCode
    {
        $tokens = [];
        $currentLineNumber = 1;
        foreach (token_get_all($sourceCode) as $token) {
            if (is_array($token)) {
                $normalizedToken = new Token(...$token);
                $currentLineNumber = $normalizedToken->getEndingLineNumber();
            } else {
                $normalizedToken = new Token(0, $token, $currentLineNumber);
            }
            $tokens[] = $normalizedToken;
        }

        return new TokenizedSourceCode($tokens);
    }
}
