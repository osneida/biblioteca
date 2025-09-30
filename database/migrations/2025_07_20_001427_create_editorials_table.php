<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('editorials', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->comment('Nombre de la editorial');
            $table->string('direccion')->nullable()->comment('Dirección de la editorial');
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('editorials');
    }
};
