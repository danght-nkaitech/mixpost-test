<?php

namespace Inovector\MixpostEnterprise\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\MixpostEnterprise\Configs\OnboardingConfig;

class DeleteUnverifiedUsers extends Command
{
    use UsesUserModel;

    protected $signature = 'mixpost-enterprise:users:delete-unverified';

    protected $description = 'Deletes users who have not verified their email after 30 days';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! app(OnboardingConfig::class)->get('email_verification')) {
            $this->comment('Email verification is disabled. No users have been deleted.');

            return self::SUCCESS;
        }

        if (! app(OnboardingConfig::class)->get('delete_unverified_users')) {
            $this->comment('Deleting unverified users is disabled. No users have been deleted.');

            return self::SUCCESS;
        }

        $cutoffDate = Carbon::now()->subDays(30);

        $deleted = self::getUserClass()::whereNull('email_verified_at')
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        $this->info("$deleted unverified users older than 30 days have been deleted successfully.");

        return self::SUCCESS;
    }
}
