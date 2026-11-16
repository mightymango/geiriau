<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('words', function (Blueprint $table) {
            $table->id();
            $table->string('cy');
            $table->string('en');
            $table->string('level')->index();          // mynediad | sylfaen | own
            $table->string('theme')->nullable();
            $table->string('pos')->nullable();         // n, v, adj, adv, phrase, ...
            $table->string('gender', 1)->nullable();   // m | f
            $table->string('note')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamp('introduced_at')->nullable();
            $table->string('intro_day', 10)->nullable()->index(); // Y-m-d study day it was introduced
            $table->timestamps();
            $table->unique(['cy', 'en']);
        });

        Schema::create('cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('word_id')->constrained()->cascadeOnDelete();
            $table->string('direction');               // recognise (cy→en) | produce (en→cy)
            $table->boolean('active')->default(false);
            $table->string('state')->default('new');
            $table->unsignedSmallInteger('step')->default(0);
            $table->float('ease')->default(2.5);
            $table->unsignedInteger('interval')->default(0);
            $table->timestamp('due_at')->nullable()->index();
            $table->unsignedInteger('reps')->default(0);
            $table->unsignedInteger('lapses')->default(0);
            $table->timestamp('last_reviewed_at')->nullable();
            $table->timestamps();
            $table->unique(['word_id', 'direction']);
        });

        Schema::create('review_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('card_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('grade');
            $table->string('state_before');
            $table->unsignedInteger('interval_before');
            $table->unsignedInteger('interval_after');
            $table->string('day', 10)->index();
            $table->timestamp('reviewed_at');
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('review_logs');
        Schema::dropIfExists('cards');
        Schema::dropIfExists('words');
    }
};
