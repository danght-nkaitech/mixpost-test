<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Inovector\Mixpost\Models\Account;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mixpost_accounts') && ! Schema::hasColumn('mixpost_accounts', 'authorized')) {
            Schema::table('mixpost_accounts', function (Blueprint $table) {
                $table->boolean('authorized')->default(false)->after('data');
            });

            Account::withoutWorkspace()->update(['authorized' => true]);
        }

        if (Schema::hasTable('mixpost_settings') && ! $this->uniqueConstraintExistsInSettingsTable()) {
            Schema::table('mixpost_settings', function (Blueprint $table) {
                $table->unique(['user_id', 'name']);
            });
        }

        if (! Schema::hasTable('mixpost_user_two_factor_auth')) {
            Schema::create('mixpost_user_two_factor_auth', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('user_id')->unsigned()->index();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->text('secret_key');
                $table->text('recovery_codes');
                $table->timestamp('confirmed_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('mixpost_pages')) {
            Schema::create('mixpost_pages', function (Blueprint $table) {
                $table->id();
                $table->uuid('uuid')->unique();
                $table->string('name')->nullable();
                $table->string('slug')->unique();
                $table->string('meta_title')->nullable();
                $table->text('meta_description')->nullable();
                $table->string('layout');
                $table->boolean('status')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('mixpost_blocks')) {
            Schema::create('mixpost_blocks', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('module');
                $table->json('content')->nullable();
                $table->boolean('status')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('mixpost_page_block')) {
            Schema::create('mixpost_page_block', function (Blueprint $table) {
                $table->id();
                $table->foreignId('page_id')->constrained('mixpost_pages')->onDelete('cascade');
                $table->foreignId('block_id')->constrained('mixpost_blocks')->onDelete('cascade');
                $table->json('options')->nullable();
                $table->integer('sort_order')->nullable();
            });
        }

        if (! Schema::hasTable('mixpost_configs')) {
            Schema::create('mixpost_configs', function (Blueprint $table) {
                $table->id();
                $table->string('group');
                $table->string('name');
                $table->json('payload')->nullable();

                $table->unique(['group', 'name']);
            });
        }
    }

    protected function uniqueConstraintExistsInSettingsTable(): bool
    {
        $connection = DB::connection()->getDoctrineSchemaManager();
        $indexes = $connection->listTableIndexes('mixpost_settings');

        foreach ($indexes as $index) {
            if ($index->isUnique() &&
                count($index->getColumns()) == 2 &&
                in_array('user_id', $index->getColumns()) &&
                in_array('name', $index->getColumns())) {
                return true;
            }
        }

        return false;
    }
};
