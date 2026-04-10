<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Inovector\Mixpost\Models\Service;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('mixpost_services')) {
            if (Schema::hasColumn('mixpost_services', 'credentials') && ! Schema::hasColumn('mixpost_services', 'configuration')) {
                Schema::table('mixpost_services', function (Blueprint $table) {
                    $table->renameColumn('credentials', 'configuration');
                });
            }

            if (! Schema::hasColumn('mixpost_services', 'active')) {
                Schema::table('mixpost_services', function (Blueprint $table) {
                    $table->boolean('active')->default(false);
                });
            }

            Service::query()->update(['active' => true]);
        }

        if (! Schema::hasTable('mixpost_user_tokens')) {
            Schema::create('mixpost_user_tokens', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('user_id')->unsigned()->index();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->string('name');
                $table->string('token', 64)->unique();
                $table->timestamp('last_used_at')->nullable();
                $table->date('expires_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('mixpost_webhooks')) {
            Schema::create('mixpost_webhooks', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->bigInteger('workspace_id')->nullable()->index(); // if null, it's a system webhook
                $table->string('name');
                $table->string('callback_url');
                $table->string('method')->default('post');
                $table->string('content_type');
                $table->tinyInteger('max_attempts')->default(1);
                $table->tinyInteger('last_delivery_status')->nullable();
                $table->text('secret')->nullable();
                $table->boolean('active')->default(false);
                $table->json('events')->nullable();
                $table->timestamps();

                $table->index(['workspace_id', 'active']);
            });
        }

        if (! Schema::hasTable('mixpost_webhook_deliveries')) {
            Schema::create('mixpost_webhook_deliveries', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->bigInteger('webhook_id')->unsigned()->index();
                $table->foreign('webhook_id')->references('id')->on('mixpost_webhooks')->onDelete('cascade');
                $table->string('event');
                $table->tinyInteger('attempts')->default(0);
                $table->tinyInteger('status');
                $table->integer('http_status')->nullable();
                $table->timestamp('resend_at')->nullable();
                $table->boolean('resent_manually')->default(false);
                $table->json('payload')->nullable();
                $table->json('response')->nullable();
                $table->timestamp('created_at')->nullable();
            });
        }

        Schema::dropIfExists('mixpost_post_version_media');
    }
};
