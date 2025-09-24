<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Editorial extends Model
{
    protected $fillable = [
        'nombre',
    ];

    public function catalogos()
    {
        return $this->hasMany(Catalogo::class);
    }
}
