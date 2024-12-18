<?php
class DateUtils {
    public static function formatGameDate(string $date): string {
        $timestamp = strtotime($date);
        return date('d/m/Y H:i', $timestamp);
    }

    public static function getTimeDifference(string $date1, string $date2 = 'now'): array {
        $datetime1 = new DateTime($date1);
        $datetime2 = new DateTime($date2);
        $interval = $datetime1->diff($datetime2);
        
        return [
            'days' => $interval->days,
            'hours' => $interval->h,
            'minutes' => $interval->i,
            'seconds' => $interval->s
        ];
    }

    public static function getRemainingTime(string $endDate): string {
        $now = new DateTime();
        $end = new DateTime($endDate);
        $interval = $now->diff($end);

        if ($interval->days > 0) {
            return $interval->format('%a jours');
        } elseif ($interval->h > 0) {
            return $interval->format('%h heures');
        } else {
            return $interval->format('%i minutes');
        }
    }

    public static function isExpired(string $date): bool {
        return strtotime($date) < time();
    }
}
