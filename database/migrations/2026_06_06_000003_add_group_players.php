<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_players', function (Blueprint $table) {
            $table->char('id', 32)->primary();
            $table->char('group_id', 32)->index();
            $table->foreign('group_id')->references('id')->on('poker_groups');
            $table->char('user_id', 32)->nullable()->index();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('name', 100);
            $table->string('nickname', 50)->nullable();
            $table->string('photo_path')->nullable();
            $table->string('role')->default('CORE'); // CORE, GUEST
            $table->string('email')->nullable();
            $table->string('invite_token', 40)->nullable()->unique();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent()->useCurrentOnUpdate();
        });

        // SQLite doesn't support ALTER COLUMN, so we rebuild game_attendees
        // to make user_id nullable and add group_player_id.
        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement('
            CREATE TABLE game_attendees_new (
                id         CHAR(32) NOT NULL,
                poker_night_id CHAR(32) NOT NULL,
                user_id    CHAR(32) NULL,
                group_player_id CHAR(32) NULL,
                placement  INTEGER NULL,
                rsvp       VARCHAR(20) NULL,
                createdAt  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            )
        ');

        DB::statement('
            INSERT INTO game_attendees_new (id, poker_night_id, user_id, placement, rsvp, createdAt)
            SELECT id, poker_night_id, user_id, placement, rsvp, createdAt
            FROM game_attendees
        ');

        DB::statement('DROP TABLE game_attendees');
        DB::statement('ALTER TABLE game_attendees_new RENAME TO game_attendees');

        DB::statement('CREATE INDEX game_attendees_poker_night_id_index ON game_attendees (poker_night_id)');
        DB::statement('CREATE INDEX game_attendees_user_id_index ON game_attendees (user_id)');
        DB::statement('CREATE INDEX game_attendees_group_player_id_index ON game_attendees (group_player_id)');

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        Schema::dropIfExists('group_players');

        // Reverse: rebuild without group_player_id, user_id NOT NULL again
        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement('
            CREATE TABLE game_attendees_old (
                id         CHAR(32) NOT NULL,
                poker_night_id CHAR(32) NOT NULL,
                user_id    CHAR(32) NOT NULL,
                placement  INTEGER NULL,
                rsvp       VARCHAR(20) NULL,
                createdAt  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id)
            )
        ');

        DB::statement('
            INSERT INTO game_attendees_old (id, poker_night_id, user_id, placement, rsvp, createdAt)
            SELECT id, poker_night_id, user_id, placement, rsvp, createdAt
            FROM game_attendees WHERE user_id IS NOT NULL
        ');

        DB::statement('DROP TABLE game_attendees');
        DB::statement('ALTER TABLE game_attendees_old RENAME TO game_attendees');

        DB::statement('CREATE INDEX game_attendees_poker_night_id_index ON game_attendees (poker_night_id)');
        DB::statement('CREATE INDEX game_attendees_user_id_index ON game_attendees (user_id)');
        DB::statement('CREATE UNIQUE INDEX game_attendees_poker_night_id_user_id_unique ON game_attendees (poker_night_id, user_id)');

        DB::statement('PRAGMA foreign_keys = ON');
    }
};
