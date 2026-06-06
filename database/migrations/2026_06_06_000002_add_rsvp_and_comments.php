<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_attendees', function (Blueprint $table) {
            // GOING, NOT_GOING, MAYBE — null means no response yet
            $table->string('rsvp')->nullable()->after('placement');
        });

        Schema::create('night_comments', function (Blueprint $table) {
            $table->char('id', 32)->primary();
            $table->char('poker_night_id', 32)->index();
            $table->foreign('poker_night_id')->references('id')->on('poker_nights');
            $table->char('user_id', 32)->index();
            $table->foreign('user_id')->references('id')->on('users');
            $table->text('message');
            $table->timestamp('createdAt')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('night_comments');
        Schema::table('game_attendees', function (Blueprint $table) {
            $table->dropColumn('rsvp');
        });
    }
};
