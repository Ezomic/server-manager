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
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('hostname');
            $table->unsignedSmallInteger('port')->default(22);
            $table->string('ssh_user')->default('root');
            $table->foreignId('ssh_key_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('web');
            $table->string('provider')->nullable();
            $table->json('tags')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('unknown');
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
