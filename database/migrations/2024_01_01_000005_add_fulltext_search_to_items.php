<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add tsvector column as a generated column (PostgreSQL 12+)
        DB::statement("
            ALTER TABLE items
            ADD COLUMN search_vector tsvector
            GENERATED ALWAYS AS (
                setweight(to_tsvector('english', coalesce(title, '')), 'A') ||
                setweight(to_tsvector('english', coalesce(description, '')), 'B')
            ) STORED
        ");

        // Create GIN index for fast full-text search
        DB::statement("
            CREATE INDEX items_search_vector_idx
            ON items
            USING GIN (search_vector)
        ");
    }

    public function down(): void
    {
        DB::statement("DROP INDEX IF EXISTS items_search_vector_idx");

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('search_vector');
        });
    }
};
