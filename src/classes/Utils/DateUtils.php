<?php
namespace App\Classes\Utils;

class DateUtils {
    public static function formatGameDate(string $date): string {
        $timestamp = strtotime($date);
        $now = time();
        $diff = $now - $timestamp;
        
        if ($diff < 60) {
            return "À l'instant";
        } elseif ($diff < 3600) {
            return "Il y a " . floor($diff / 60) . " min";
        } elseif ($diff < 86400) {
            return "Il y a " . floor($diff / 3600) . "h";
        } elseif ($diff < 604800) {
            return "Il y a " . floor($diff / 86400) . " jours";
        } else {
            return date('d/m/Y', $timestamp);
        }
    }

    public static function getRemainingTime(string $targetDate): string {
        $target = strtotime($targetDate);
        $now = time();
        $diff = $target - $now;

        if ($diff <= 0) {
            return "Expiré";
        } elseif ($diff < 3600) {
            return ceil($diff / 60) . " min";
        } elseif ($diff < 86400) {
            return ceil($diff / 3600) . "h";
        } else {
            return ceil($diff / 86400) . " jours";
        }
    }

    public static function getGameDays(int $timestamp): int {
        $hoursSinceStart = floor((time() - $timestamp) / 3600);
        return max(0, $hoursSinceStart);
    }
}
