<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasApiFeatures; // Importa el nuevo Trait

class Editorial extends Model
{
    use HasApiFeatures;

    protected $fillable = [
        'nombre',
        'direccion'
    ];

    public function catalogos()
    {
        return $this->hasMany(Catalogo::class);
    }
}
