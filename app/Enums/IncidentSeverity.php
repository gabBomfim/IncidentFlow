<?php

namespace App\Enums;

enum IncidentSeverity: string
{
    case Sev1 = 'sev1';
    case Sev2 = 'sev2';
    case Sev3 = 'sev3';
    case Sev4 = 'sev4';

    public function label(): string
    {
        return match ($this) {
            self::Sev1 => 'SEV-1 Critical',
            self::Sev2 => 'SEV-2 High',
            self::Sev3 => 'SEV-3 Moderate',
            self::Sev4 => 'SEV-4 Low',
        };
    }

    public function targetMinutes(): int
    {
        return match ($this) {
            self::Sev1 => 15,
            self::Sev2 => 30,
            self::Sev3 => 60,
            self::Sev4 => 240,
        };
    }
}
