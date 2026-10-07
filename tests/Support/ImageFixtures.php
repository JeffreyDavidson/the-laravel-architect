<?php

namespace Tests\Support;

class ImageFixtures
{
    /**
     * A valid, blank one-bit grayscale PNG. Its compressed rows take a few kilobytes even at tens
     * of megapixels, so tests can upload very large images without large files.
     */
    public static function blankPng(int $width, int $height): string
    {
        $chunk = fn (string $type, string $data): string => pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));
        $rows = str_repeat(str_repeat("\0", 1 + intdiv($width + 7, 8)), $height);

        return "\x89PNG\r\n\x1a\n"
            .$chunk('IHDR', pack('NNCCCCC', $width, $height, 1, 0, 0, 0, 0))
            .$chunk('IDAT', (string) gzcompress($rows))
            .$chunk('IEND', '');
    }
}
