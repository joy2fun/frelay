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
        Schema::create('endpoint_targets', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('endpoint_id');
            $table->string('title')->default('');
            $table->string('uri')->default('');
            $table->text('rule')->nullable();
            $table->string('method')->default('');
            $table->json('headers')->nullable();
            $table->text('body')->nullable();
            $table->unsignedTinyInteger('enabled')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('endpoint_targets');
    }
};
