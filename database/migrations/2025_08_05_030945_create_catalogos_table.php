<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\TipoDocumentoEnum;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalogos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha_registro');
            $table->enum('tipo_documento', array_column(TipoDocumentoEnum::cases(), 'name')); //solo los tipos de documentos que se encuentra en TipoDocumentoEnum
            $table->string('isbn', 100)->unique();
            $table->string('titulo');
            $table->string('sub_titulo')->nullable();

            $table->unsignedBigInteger('autor_id')->index();
            $table->foreign('autor_id')->references('id')->on('autors')->onDelete('restrict');
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
