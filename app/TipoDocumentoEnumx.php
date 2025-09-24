<?php

namespace App\Enums;

enum TipoDocumentoEnum
{
    case Libro;
    case Revista;
    case Novela;

    public function label(): string
    {
        return match ($this) {
            self::Libro   => __('Libro'),
            self::Revista => __('Revista'),
            self::Novela  => __('Novela'),
        };
    }
}
