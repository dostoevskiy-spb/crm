<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::table('individual', function (Blueprint $table) {
            // Add new UUID columns
            if (!Schema::hasColumn('individual', 'uid')) {
                $table->uuid('uid')->nullable()->after('id');
            }
            if (!Schema::hasColumn('individual', 'creator_uid')) {
                $table->uuid('creator_uid')->nullable()->after('creator_id');
            }
        });

        if ($driver === 'pgsql') {
            // Enable required extension for UUIDs in PostgreSQL
            DB::statement('CREATE EXTENSION IF NOT EXISTS pgcrypto');

            // Backfill uid values for all rows
            DB::statement("UPDATE individual SET uid = gen_random_uuid() WHERE uid IS NULL");

            // Backfill creator_uid from creator_id using self-join
            DB::statement(<<<SQL
                UPDATE individual i
                SET creator_uid = i2.uid
                FROM individual i2
                WHERE i.creator_id IS NOT NULL AND i.creator_id = i2.id
            SQL);

            // Replace primary key from id -> uid
            // 1) Drop existing PK
            DB::statement('ALTER TABLE individual DROP CONSTRAINT IF EXISTS individual_pkey');
            // 2) Set new PK (will enforce NOT NULL)
            DB::statement('ALTER TABLE individual ADD CONSTRAINT individual_pkey PRIMARY KEY (uid)');

            // Indexes
            Schema::table('individual', function (Blueprint $table) {
                $table->index('creator_uid');
            });

            // Drop old id column and creator_id
            Schema::table('individual', function (Blueprint $table) {
                $table->dropColumn('id');
                $table->dropColumn('creator_id');
            });
        } else {
            // Generic/PHP fallback (e.g., SQLite in tests): backfill UUIDs via PHP
            $rows = DB::table('individual')->select('id', 'uid', 'creator_id')->get();

            // First pass: ensure uid for every row
            foreach ($rows as $row) {
                if ($row->uid === null) {
                    DB::table('individual')
                        ->where('id', $row->id)
                        ->update(['uid' => (string) Str::uuid()]);
                }
            }

            // Build mapping id->uid for creator_uid backfill
            $map = DB::table('individual')->select('id', 'uid')->get()->pluck('uid', 'id');
            foreach ($rows as $row) {
                if ($row->creator_id !== null) {
                    $creatorUid = $map[$row->creator_id] ?? null;
                    if ($creatorUid) {
                        DB::table('individual')
                            ->where('id', $row->id)
                            ->update(['creator_uid' => $creatorUid]);
                    }
                }
            }

            // Indexes (safe in sqlite). Ensure uid uniqueness even if not a PK
            Schema::table('individual', function (Blueprint $table) {
                $table->unique('uid');
                $table->index('creator_uid');
            });

            // NOTE: We intentionally do not alter primary key or drop columns in SQLite path
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'pgsql') {
            // Recreate old columns
            Schema::table('individual', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('creator_id')->nullable();
            });

            // Backfill id (cannot restore originals). We'll generate sequential ids
            // WARNING: Data order-based id generation is a best-effort for down migration.
            DB::statement(<<<SQL
                CREATE TEMP TABLE tmp_ids AS SELECT uid, ROW_NUMBER() OVER (ORDER BY created_at, uid) AS new_id FROM individual;
                UPDATE individual i SET id = t.new_id FROM tmp_ids t WHERE i.uid = t.uid;
                DROP TABLE tmp_ids;
            SQL);

            // Backfill creator_id by joining creator_uid->id
            DB::statement(<<<SQL
                UPDATE individual i
                SET creator_id = c.id
                FROM individual c
                WHERE i.creator_uid IS NOT NULL AND i.creator_uid = c.uid
            SQL);

            // Restore PK to id
            DB::statement('ALTER TABLE individual DROP CONSTRAINT IF EXISTS individual_pkey');
            DB::statement('ALTER TABLE individual ADD CONSTRAINT individual_pkey PRIMARY KEY (id)');

            // Drop new columns
            Schema::table('individual', function (Blueprint $table) {
                $table->dropIndex(['creator_uid']);
                if (Schema::hasColumn('individual', 'uid')) {
                    $table->dropColumn('uid');
                }
                if (Schema::hasColumn('individual', 'creator_uid')) {
                    $table->dropColumn('creator_uid');
                }
            });
        } else {
            // No-op for SQLite (tests).
        }
    }
};
