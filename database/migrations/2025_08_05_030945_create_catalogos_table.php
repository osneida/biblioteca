<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogos', function (Blueprint $table) {
            $table->id();
            $table->integer('tipo_documento'); //, array_column(TipoDocumentoEnum::cases(), 'name')); //solo los tipos de documentos que se encuentra en TipoDocumentoEnum
            $table->string('isbn', 50)->unique()->nullable();
            $table->string('titulo');
            $table->string('subtitulo')->nullable();
            $table->date('fecha_publicacion');
            $table->text('descripcion_fisica')->nullable();
            $table->text('notas')->nullable();
            $table->unsignedBigInteger('editorial_id')->index();
            $table->foreign('editorial_id')->references('id')->on('editorials')->onDelete('restrict');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalogos');
    }
};
