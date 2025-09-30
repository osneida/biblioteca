<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('autor_catalogo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('autor_id')->index();
            $table->unsignedBigInteger('catalogo_id')->index();
            $table->foreign('autor_id')->references('id')->on('autors')->onDelete('cascade');
            $table->foreign('catalogo_id')->references('id')->on('catalogos')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['autor_id', 'catalogo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('autor_catalogo');
    }
};
