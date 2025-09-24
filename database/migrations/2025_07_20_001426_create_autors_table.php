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
            $table->string('nombre')->comment('Nombre of the author');
            $table->string('nacionalidad', 1)->comment('Nacionalidad of the author');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('autors');
    }
};
