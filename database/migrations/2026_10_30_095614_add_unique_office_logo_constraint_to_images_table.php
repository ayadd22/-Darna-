<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE UNIQUE INDEX images_one_logo_per_office
            ON images (imageable_id)
            WHERE imageable_type = 'office'
        ");
    }

    public function down(): void
    {
        DB::statement("
            DROP INDEX IF EXISTS images_one_logo_per_office
        ");
    }
};