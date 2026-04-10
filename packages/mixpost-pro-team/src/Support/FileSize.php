<?php

namespace Inovector\Mixpost\Support;

class FileSize
{
    public static function mbToBytes(int|float $mb): int
    {
        return (int) ($mb * 1024 * 1024);
    }

    public static function mbToKb(int|float $mb): int
    {
        return (int) ($mb * 1024);
    }

    public static function bytesToMb(int|float $bytes): float
    {
        return $bytes / 1024 / 1024;
    }

    public static function kbToMb(int|float $kb): float
    {
        return $kb / 1024;
    }

    public static function bytesToKb(int|float $bytes): float
    {
        return $bytes / 1024;
    }

    public static function kbToBytes(int|float $kb): int
    {
        return (int) ($kb * 1024);
    }
}
