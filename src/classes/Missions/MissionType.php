<?php
namespace App\Classes\Missions;

class MissionType {
    public const CULTIVATION = 'cultivation';
    public const TRADING = 'trading';
    public const SOCIAL = 'social';

    public static function getTypes(): array {
        return [
            self::CULTIVATION => [
                'name' => 'Culture',
                'description' => 'Missions liées à la culture des plantes',
                'icon' => '🌱'
            ],
            self::TRADING => [
                'name' => 'Commerce',
                'description' => 'Missions liées aux échanges',
                'icon' => '🤝'
            ],
            self::SOCIAL => [
                'name' => 'Social',
                'description' => 'Missions liées aux interactions sociales',
                'icon' => '💬'
            ]
        ];
    }

    public static function getTypeInfo(string $type): ?array {
        $types = self::getTypes();
        return $types[$type] ?? null;
    }

    public static function isValid(string $type): bool {
        return isset(self::getTypes()[$type]);
    }
}