<?php

use App\Services\WordBank;
use Illuminate\Database\Migrations\Migration;

/**
 * Loads database/data/words.txt on the device. NativePHP runs migrations when the app starts,
 * so to ship more words later: edit words.txt and add a new migration that calls WordBank::sync().
 * Existing words (matched on Welsh + English) keep their progress.
 */
return new class extends Migration
{
    public function up(): void
    {
        WordBank::sync(database_path('data/words.txt'));
    }

    public function down(): void {}
};
