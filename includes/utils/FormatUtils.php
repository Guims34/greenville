<?php
class FormatUtils {
    public static function formatNumber(int $number): string {
        return number_format($number, 0, ',', ' ');
    }

    public static function formatPercentage(float $value): string {
        return round($value, 1) . '%';
    }

    public static function formatCurrency(int $amount): string {
        return self::formatNumber($amount) . ' 🪙';
    }

    public static function formatPremiumCurrency(int $amount): string {
        return self::formatNumber($amount) . ' 💎';
    }

    public static function getProgressColor(float $percentage): string {
        return match(true) {
            $percentage >= 75 => 'bg-green-500',
            $percentage >= 50 => 'bg-yellow-500',
            $percentage >= 25 => 'bg-orange-500',
            default => 'bg-red-500'
        };
    }
}
