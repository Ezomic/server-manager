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
        Schema::create('metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->decimal('cpu_percent', 5, 2);
            $table->unsignedBigInteger('memory_used_mb');
            $table->unsignedBigInteger('memory_total_mb');
            $table->unsignedBigInteger('disk_used_mb');
            $table->unsignedBigInteger('disk_total_mb');
            $table->decimal('load_avg', 8, 2);
            $table->timestamp('recorded_at')->index();
            $table->timestamps();

            $table->index(['server_id', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('metrics');
    }
};
