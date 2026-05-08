<?php

namespace App\Enums;

enum CourseStatus: string
{
    case Draft = 'draft';
    case Build = 'build';
    case Published = 'published';

    public static function authoringStatuses(): array
    {
        return [
            self::Build->value,
            self::Published->value,
        ];
    }

    public static function publicStatuses(): array
    {
        return [
            self::Published->value,
        ];
    }
}
