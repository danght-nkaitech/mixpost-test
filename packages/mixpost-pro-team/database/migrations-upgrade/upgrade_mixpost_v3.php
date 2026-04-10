<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inovector\Mixpost\Enums\WorkspaceUserRole;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('mixpost_workspace_user')) {
            if (! Schema::hasColumn('mixpost_workspace_user', 'can_approve')) {
                Schema::table('mixpost_workspace_user', function (Blueprint $table) {
                    $table->boolean('can_approve')->default(false);
                });
            }
        }

        if (! Schema::hasTable('mixpost_post_activities')) {
            Schema::create('mixpost_post_activities', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->foreignId('post_id')->constrained('mixpost_posts')->onDelete('cascade');
                $table->bigInteger('user_id'); // 0 for system
                $table->bigInteger('parent_id')->nullable()->constrained('mixpost_post_activities')->onDelete('cascade');
                $table->tinyInteger('type');
                $table->json('data')->nullable();
                $table->longText('text')->nullable(); // for comments
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('mixpost_post_activity_reactions')) {
            Schema::create('mixpost_post_activity_reactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('activity_id')->constrained('mixpost_post_activities')->onDelete('cascade');
                $table->bigInteger('user_id')->unsigned();
                $table->string('reaction');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('mixpost_post_activities_ns')) {
            Schema::create('mixpost_post_activities_ns', function (Blueprint $table) {
                $table->id();
                $table->foreignId('post_id')->constrained('mixpost_posts')->onDelete('cascade');
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->timestamps();
            });
        }

        DB::table('mixpost_workspace_user')
            ->where('role', WorkspaceUserRole::ADMIN->value)
            ->update(['can_approve' => true]);
    }
};
