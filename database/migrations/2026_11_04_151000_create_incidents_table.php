<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('title');
            $table->string('service');
            $table->string('severity');
            $table->string('status')->index();
            $table->text('summary');
            $table->string('owner');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['severity', 'status']);
        });

        Schema::create('incident_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->text('message');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incident_events');
        Schema::dropIfExists('incidents');
    }
};
