<?php

namespace App\Enums;

enum DepartmentEnum: string
{
    case DEVELOPMENT = 'Development';
    case TESTING = 'Testing';
    case SALES = 'Sales';
    case MARKETING = 'Marketing';
    case APPLICATION_INTEGRATION = 'Application Integration';
    case HIRING = 'Hiring';

    /**
     * Return all department values
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
