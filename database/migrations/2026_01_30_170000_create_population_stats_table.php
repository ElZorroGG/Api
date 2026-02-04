<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('islas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->string('codigo')->nullable()->unique();
            $table->timestamps();
        });

        Schema::create('lugares', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->nullable();
            $table->string('codigo_lugar')->nullable()->unique();
            $table->foreignId('isla_id')->nullable()->constrained('islas')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('population_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lugar_id')->nullable()->constrained('lugares')->nullOnDelete();
            $table->foreignId('isla_id')->nullable()->constrained('islas')->nullOnDelete();
            $table->integer('ano')->index();
            $table->string('genero')->nullable()->index();
            $table->string('edad')->nullable()->index();
            $table->bigInteger('poblacion')->nullable();
            $table->timestamps();
            $table->unique(['lugar_id','isla_id','ano','genero','edad'],'population_unique_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('population_stats');
        Schema::dropIfExists('lugares');
        Schema::dropIfExists('islas');
    }
};
