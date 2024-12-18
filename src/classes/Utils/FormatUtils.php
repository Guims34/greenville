<?php
namespace App\Classes\Utils;

class FormatUtils {
    public static function sanitizeString(string $str): string {
        return htmlspecialchars(strip_tags(trim($str)));
    }

    public static function validateInt($value): ?int {
        return filter_var($value, FILTER_VALIDATE_INT);
    }

    public static function formatNumber(int $number): string {
        if ($number >= 1000000) {
            return round($number / 1000000, 1) . 'M';
        } elseif ($number >= 1000) {
            return round($number / 1000, 1) . 'k';
        }
        return (string)$number;
    }

    public static function formatPercentage(float $value): string {
        return round($value * 100, 1) . '%';
    }
}
