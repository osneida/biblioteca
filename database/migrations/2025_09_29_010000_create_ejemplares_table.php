<?php

use App\Enums\EstatusDisponibilidadEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ejemplares', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('catalogo_id')->index();
            $table->integer('nro_ejemplar')->comment('Número de ejemplar dentro del catálogo');
            $table->string('codigo', 10)->unique()->comment('Código único del ejemplar');
            $table->string('estatus', 1)->default(EstatusDisponibilidadEnum::Disponible); // Enum: disponible, prestado, reparacion, perdido, retrasado
            $table->timestamps();

            $table->foreign('catalogo_id')->references('id')->on('catalogos')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ejemplares');
    }
};