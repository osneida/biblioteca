<?php

namespace App\Enums;

enum NacionalidadEnum: string
{
    case Venezolano = "V";
    case Extranjero = "E";

    public function label(): string
    {
        return match ($this) {
            self::Venezolano => __('Venezolano'),
            self::Extranjero => __('Extranjero'),
        };
    }

    /**
     * Return an array of enum scalar values (e.g. ['V', 'E']).
     */
    public static function values(): array
    {
        return array_map(fn(NacionalidadEnum $c) => $c->value, self::cases());
    }
}
