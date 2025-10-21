<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use App\Models\Traits\HasApiFeatures; // Importa el nuevo Trait

class Editorial extends Model
{
    use HasApiFeatures, HasFactory;

    protected $fillable = [
        'nombre',
        'direccion'
    ];

    public function catalogos()
    {
        return $this->hasMany(Catalogo::class);
    }
}
