<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('aromes', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('marque');
            $table->integer('prix');
            $table->integer('categorie');
            $table->integer('quantite');
            $table->integer('stock');
            $table->string('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aromes');
    }
};
