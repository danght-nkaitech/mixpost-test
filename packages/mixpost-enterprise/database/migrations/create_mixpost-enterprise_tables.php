<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Inovector\MixpostEnterprise\Enums\WorkspaceAccessStatus;

return new class extends Migration
{
    use \Inovector\Mixpost\Concerns\UsesUserModel;

    private $usersTableName;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('mixpost_workspaces', function (Blueprint $table) {
            $table->after('hex_color', function (Blueprint $table) {
                $table->bigInteger('owner_id')->nullable();
                $table->string('access_status', 30)->default(WorkspaceAccessStatus::SUBSCRIPTION->value);
                $table->bigInteger('generic_subscription_plan_id')->nullable();
                $table->boolean('generic_subscription_free')->default(false);
                $table->timestamp('generic_trial_ends_at')->nullable();
                $table->string('pm_type')->nullable();
                $table->string('pm_card_brand')->nullable();
                $table->string('pm_card_last_four', 4)->nullable();
                $table->string('pm_card_expires', 7)->nullable();
                $table->text('billing_info')->nullable();
                $table->string('stripe_id')->nullable()->index();
                $table->json('limits')->nullable();
            });
        });

        Schema::create('mixpost_e_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->bigInteger('monthly_amount')->default(0);
            $table->string('monthly_platform_plan_id')->nullable();
            $table->bigInteger('yearly_amount')->default(0);
            $table->string('yearly_platform_plan_id')->nullable();
            $table->boolean('enabled')->default(0);
            $table->tinyInteger('sort_order')->default(0);
            $table->json('limits')->nullable();
            $table->timestamps();
        });

        Schema::create('mixpost_e_payment_platforms', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->longText('credentials');
            $table->json('options')->nullable();
            $table->boolean('enabled')->default(false);
        });

        Schema::create('mixpost_e_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('workspace_id')->unsigned()->index();
            $table->string('name');
            $table->string('platform_subscription_id')->unique();
            $table->string('platform_plan_id')->nullable();
            $table->string('status');
            $table->json('platform_data')->nullable();
            $table->integer('quantity');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('paused_from')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('mixpost_e_receipts', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->bigInteger('workspace_id')->unsigned()->index();
            $table->string('transaction_id');
            $table->string('invoice_number')->unique();
            $table->string('platform_plan_id')->nullable();
            $table->string('amount');
            $table->string('tax');
            $table->string('currency', 3);
            $table->integer('quantity');
            $table->string('receipt_url')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('paid_at');
            $table->timestamps();

            $table->index(['workspace_id', 'invoice_number']);
        });

        Schema::create('mixpost_e_invitations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique();
            $table->bigInteger('workspace_id')->unsigned()->index();
            $table->foreign('workspace_id')->references('id')->on('mixpost_workspaces')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('invited_by')->unsigned()->index();
            $table->foreign('invited_by')->references('id')->on($this->getUsersTableName())->onDelete('cascade');
            $table->bigInteger('user_id')->nullable();
            $table->string('email');
            $table->string('role');
            $table->boolean('can_approve')->default(false);
            $table->timestamps();
        });

        Schema::create('mixpost_e_usage_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('workspace_id')->unsigned()->index();
            $table->string('type');
            $table->timestamp('created_at');

            $table->index(['workspace_id', 'type']);
        });

        Schema::create('mixpost_e_workspace_services', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('workspace_id')->unsigned()->index();
            $table->string('name');
            $table->longText('configuration');
            $table->boolean('active')->default(false);

            $table->unique(['workspace_id', 'name'], 'workspace_service_name_unique');
        });

        Schema::create('mixpost_e_customers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->index();
            $table->string('platform_customer_id')->unique();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('mixpost_workspaces', function (Blueprint $table) {
            $table->dropColumn([
                'owner_id',
                'access_status',
                'generic_subscription_plan_id',
                'generic_subscription_free',
                'generic_trial_ends_at',
                'pm_type',
                'pm_card_brand',
                'pm_card_last_four',
                'pm_card_expires',
                'billing_info',
                'stripe_id',
                'limits',
            ]);
        });
        Schema::dropIfExists('mixpost_e_plans');
        Schema::dropIfExists('mixpost_e_payment_platforms');
        Schema::dropIfExists('mixpost_e_subscriptions');
        Schema::dropIfExists('mixpost_e_receipts');
        Schema::dropIfExists('mixpost_e_invitations');
        Schema::dropIfExists('mixpost_e_usage_records');
        Schema::dropIfExists('mixpost_e_workspace_services');
        Schema::dropIfExists('mixpost_e_customers');
    }

    protected function getUsersTableName()
    {
        if ($this->usersTableName) {
            return $this->usersTableName;
        }

        return $this->usersTableName = app(self::getUserClass())->getTable();
    }
};
