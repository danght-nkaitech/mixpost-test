<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('mixpost_shortened_urls')) {
            Schema::create('mixpost_shortened_urls', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('workspace_id')->unsigned()->index();
                $table->foreign('workspace_id')->references('id')->on('mixpost_workspaces')->onUpdate('cascade')->onDelete('cascade');
                $table->string('provider');
                $table->string('original_url');
                $table->string('short_url');
                $table->timestamp('created_at');

                $table->index(['workspace_id', 'original_url']);
                $table->index(['workspace_id', 'short_url']);
            });
        }
    }
};
