<?php

namespace App\Enums;

enum TipoDocumentoEnum: int
{
    case Libro     = 1;
    case Revista   = 2;
    case Novela    = 3;

    public function label(): string
    {
        return match ($this) {
            self::Libro   => __('Libro'),
            self::Revista => __('Revista'),
            self::Novela  => __('Novela'),
        };
    }
}
