<?php

namespace App\Srs;

final class AnswerChecker
{
    private const ACCENTS = [
        'â' => 'a', 'ê' => 'e', 'î' => 'i', 'ô' => 'o', 'û' => 'u', 'ŵ' => 'w', 'ŷ' => 'y',
        'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u', 'á' => 'a', 'é' => 'e', 'à' => 'a', 'è' => 'e',
    ];

    /** @return 'exact'|'close'|'wrong' */
    public function check(string $typed, string $answer): string
    {
        $typed = $this->normalise($typed);

        foreach (array_map('trim', explode('/', $answer)) as $option) {
            $option = $this->normalise($option);

            if ($typed === $option) {
                return 'exact';
            }

            if (strtr($typed, self::ACCENTS) === strtr($option, self::ACCENTS)) {
                return 'close';
            }
        }

        return 'wrong';
    }

    private function normalise(string $text): string
    {
        return preg_replace('/[^\p{L}\p{N} ]/u', '', mb_strtolower(trim($text)));
    }
}
