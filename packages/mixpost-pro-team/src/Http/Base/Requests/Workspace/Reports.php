<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Inovector\Mixpost\Contracts\ProviderReports;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Models\Account;
use Inovector\Mixpost\Reports\BlueskyReports;
use Inovector\Mixpost\Reports\FacebookGroupReports;
use Inovector\Mixpost\Reports\FacebookPageReports;
use Inovector\Mixpost\Reports\InstagramReports;
use Inovector\Mixpost\Reports\LinkedinPageReports;
use Inovector\Mixpost\Reports\LinkedinReports;
use Inovector\Mixpost\Reports\MastodonReports;
use Inovector\Mixpost\Reports\PinterestReports;
use Inovector\Mixpost\Reports\ThreadsReports;
use Inovector\Mixpost\Reports\TikTokReports;
use Inovector\Mixpost\Reports\TwitterReports;
use Inovector\Mixpost\Reports\YoutubeReports;

class Reports extends FormRequest
{
    public function rules(): array
    {
        return [
            'account_id' => ['required', 'integer', WorkspaceManager::existsRule('mixpost_accounts', 'id')],
            'period' => ['required', 'string', Rule::in(['7_days', '30_days', '90_days'])],
        ];
    }

    public function handle(): array
    {
        $account = Account::find($this->get('account_id'));

        $providerReports = match ($account->provider) {
            'twitter' => TwitterReports::class,
            'facebook_page' => FacebookPageReports::class,
            'facebook_group' => FacebookGroupReports::class,
            'instagram' => InstagramReports::class,
            'threads' => ThreadsReports::class,
            'mastodon' => MastodonReports::class,
            'pinterest' => PinterestReports::class,
            'linkedin' => LinkedinReports::class,
            'linkedin_page' => LinkedinPageReports::class,
            'tiktok' => TikTokReports::class,
            'youtube' => YoutubeReports::class,
            'bluesky' => BlueskyReports::class,
            default => null
        };

        if (! $providerReports) {
            return [];
        }

        $providerReports = (new $providerReports);

        if (! $providerReports instanceof ProviderReports) {
            throw new \Exception('The provider reports must be an instance of ProviderReports');
        }

        return $providerReports($account, $this->get('period', ''));
    }
}
