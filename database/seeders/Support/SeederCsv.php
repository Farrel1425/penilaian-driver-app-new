<?php

namespace Database\Seeders\Support;

use Generator;
use RuntimeException;
use SplFileObject;

final class SeederCsv
{
    /** @return Generator<int, array<string, string|null>> */
    public static function rows(string $filename): Generator
    {
        $file = new SplFileObject(database_path("seeders/data/{$filename}"));
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        $headers = null;

        foreach ($file as $lineNumber => $values) {
            if ($values === false || $values === [null]) {
                continue;
            }

            if ($headers === null) {
                $headers = array_map(
                    static fn (?string $header): string => trim((string) $header),
                    $values,
                );

                continue;
            }

            if (count($headers) !== count($values)) {
                throw new RuntimeException(sprintf(
                    'Jumlah kolom %s pada baris %d tidak sesuai header.',
                    $filename,
                    $lineNumber + 1,
                ));
            }

            $row = array_combine($headers, $values);

            if ($row === false) {
                throw new RuntimeException("Data {$filename} tidak dapat dibaca.");
            }

            yield array_map(
                static fn (?string $value): ?string => ($value = trim((string) $value)) === '' ? null : $value,
                $row,
            );
        }
    }
}
