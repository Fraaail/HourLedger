<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entry_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type')->default('missing_entry');
            $table->string('title');
            $table->text('message');
            $table->date('date');
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->unique(['type', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entry_notifications');
    }
};
