<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->char('id', 32)->primary();
            $table->string('username')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('USER'); // USER, ADMIN, SUPERADMIN
            $table->boolean('isActive')->default(true);
            $table->string('avatar_url')->nullable();
            $table->rememberToken();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('poker_groups', function (Blueprint $table) {
            $table->char('id', 32)->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->char('owner_id', 32)->index();
            $table->foreign('owner_id')->references('id')->on('users');
            $table->string('invite_code', 10)->unique();
            $table->boolean('isActive')->default(true);
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('group_members', function (Blueprint $table) {
            $table->char('id', 32)->primary();
            $table->char('group_id', 32)->index();
            $table->foreign('group_id')->references('id')->on('poker_groups');
            $table->char('user_id', 32)->index();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('role')->default('MEMBER'); // OWNER, MEMBER
            $table->timestamp('joined_at')->useCurrent();
            $table->unique(['group_id', 'user_id']);
        });

        Schema::create('poker_nights', function (Blueprint $table) {
            $table->char('id', 32)->primary();
            $table->char('group_id', 32)->index();
            $table->foreign('group_id')->references('id')->on('poker_groups');
            $table->char('created_by', 32)->index();
            $table->foreign('created_by')->references('id')->on('users');
            $table->string('title');
            $table->text('notes')->nullable();
            $table->dateTime('scheduled_at');
            $table->dateTime('played_at')->nullable();
            $table->string('status')->default('SCHEDULED'); // SCHEDULED, COMPLETED, CANCELLED
            $table->decimal('buy_in', 8, 2)->nullable();
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->useCurrent()->useCurrentOnUpdate();
        });

        Schema::create('game_attendees', function (Blueprint $table) {
            $table->char('id', 32)->primary();
            $table->char('poker_night_id', 32)->index();
            $table->foreign('poker_night_id')->references('id')->on('poker_nights');
            $table->char('user_id', 32)->index();
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedTinyInteger('placement')->nullable(); // 1 = winner
            $table->timestamp('createdAt')->useCurrent();
            $table->unique(['poker_night_id', 'user_id']);
        });

        Schema::create('game_images', function (Blueprint $table) {
            $table->char('id', 32)->primary();
            $table->char('poker_night_id', 32)->index();
            $table->foreign('poker_night_id')->references('id')->on('poker_nights');
            $table->char('uploaded_by', 32)->index();
            $table->foreign('uploaded_by')->references('id')->on('users');
            $table->string('file_path');
            $table->string('caption')->nullable();
            $table->boolean('is_cover')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamp('createdAt')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_images');
        Schema::dropIfExists('game_attendees');
        Schema::dropIfExists('poker_nights');
        Schema::dropIfExists('group_members');
        Schema::dropIfExists('poker_groups');
        Schema::dropIfExists('users');
    }
};
