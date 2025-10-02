<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('autors', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique()->comment('Nombre of the author');
            $table->string('nacionalidad', 1)->comment('Nacionalidad of the author');
            $table->date('fecha_nacimiento')->nullable()->comment('Fecha de nacimiento del autor');
            $table->date('fecha_fallecimiento')->nullable()->comment('Fecha de fallecimiento del autor');
            $table->text('biografia')->nullable()->comment('Biografía del autor');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('autors');
    }
};
