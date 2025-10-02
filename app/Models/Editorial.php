<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Editorial extends Api
{
    protected $fillable = [
        'nombre',
        'direccion'
    ];

    public function catalogos()
    {
        return $this->hasMany(Catalogo::class);
    }
}
