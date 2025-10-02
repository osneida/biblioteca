<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasGlobalScopes;

class Editorial extends Model
{
    use HasGlobalScopes;

    protected $fillable = [
        'nombre',
        'direccion'
    ];

    public function catalogos()
    {
        return $this->hasMany(Catalogo::class);
    }
}
