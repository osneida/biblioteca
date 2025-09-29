<?php

namespace App\Enums;

enum TipoDocumentoEnum: int
{
    case Libro     = 1;
    case Revista   = 2;
    case Novela    = 3;
    case Tesis     = 4;
    case Pretesis  = 5;
    case Periódico = 6;
    case Película  = 7;
    case Música    = 8;

    public function label(): string
    {
        return match ($this) {
            self::Libro     => __('Libro'),
            self::Revista   => __('Revista'),
            self::Novela    => __('Novela'),
            self::Tesis     => __('Tesis'),
            self::Pretesis  => __('Pretesis'),
            self::Periódico => __('Periódico'),
            self::Película  => __('Película'),
            self::Música    => __('Música'),
        };
    }
}
