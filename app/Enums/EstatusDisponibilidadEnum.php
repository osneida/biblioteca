<?php

namespace App\Enums;

enum EstatusDisponibilidadEnum: string
{
    case Disponible = "D";
    case Prestado   = "P";
    case Reparación = "R";
    case Perdido    = "X";
    case Retrasado  = "T";

    public function label(): string
    {
        return match ($this) {
            self::Disponible => __('Disponible'),
            self::Prestado   => __('Prestado'),
            self::Reparación => __('Reparación'),
            self::Perdido    => __('Perdido'),
            self::Retrasado  => __('Retrasado'),
        };
    }

    /**
     * Return an array of enum scalar values (e.g. ['V', 'E']).
     */
    public static function values(): array
    {
        return array_map(fn(EstatusDisponibilidadEnum $c) => $c->value, self::cases());
    }
}
