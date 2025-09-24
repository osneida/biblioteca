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
}
