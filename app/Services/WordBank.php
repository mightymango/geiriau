<?php

namespace App\Services;

use App\Models\Word;

/**
 * Reads the plain-text word bank (database/data/words.txt).
 *
 * Format — one word per line, fields separated by "|":
 *   Welsh | English | part of speech | gender (m/f) | note
 * Directives:
 *   @level mynediad      sets the level for following lines
 *   @theme Family        sets the theme for following lines
 *   # comment
 */
class WordBank
{
    /** @return array<int, array<string, mixed>> */
    public static function parse(string $text): array
    {
        $words = [];
        $level = 'sylfaen';
        $theme = null;
        $position = 0;

        foreach (preg_split('/\R/u', $text) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            if (preg_match('/^@level\s+(.+)$/u', $line, $m)) {
                $level = strtolower(trim($m[1]));

                continue;
            }
            if (preg_match('/^@theme\s+(.+)$/u', $line, $m)) {
                $theme = trim($m[1]);

                continue;
            }

            $parts = array_map('trim', explode('|', $line));
            if (count($parts) < 2 || $parts[0] === '' || $parts[1] === '') {
                continue;
            }

            $gender = strtolower($parts[3] ?? '');

            $words[] = [
                'cy' => $parts[0],
                'en' => $parts[1],
                'pos' => ($parts[2] ?? '') ?: null,
                'gender' => in_array($gender, ['m', 'f'], true) ? $gender : null,
                'note' => ($parts[4] ?? '') ?: null,
                'level' => $level,
                'theme' => $theme,
                'position' => ++$position,
            ];
        }

        return $words;
    }

    public static function sync(string $path): int
    {
        $rows = static::parse(file_get_contents($path));

        foreach ($rows as $row) {
            $word = Word::query()->firstOrNew(['cy' => $row['cy'], 'en' => $row['en']]);
            $word->fill($row)->save();
        }

        return count($rows);
    }
}
