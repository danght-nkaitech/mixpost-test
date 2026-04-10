<?php

namespace Inovector\Mixpost\SocialProviders\Meta\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Inovector\Mixpost\Contracts\QueueWorkspaceAware;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Models\Account;
use Inovector\Mixpost\Models\ImportedPost;
use Inovector\Mixpost\Models\Metric;

class ProcessInstagramMetricsJob implements QueueWorkspaceAware, ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $deleteWhenMissingModels = true;

    public Account $account;

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    public function handle()
    {
        $items = ImportedPost::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(JSON_EXTRACT(metrics, "$.like_count")) as likes'),
            DB::raw('SUM(JSON_EXTRACT(metrics, "$.comments_count")) as comments'))
            ->where('account_id', $this->account->id)
            ->groupBy('date')
            ->cursor();

        $data = $items->map(function ($item) {
            return [
                'workspace_id' => WorkspaceManager::current()->id,
                'account_id' => $this->account->id,
                'date' => $item->date,
                'data' => json_encode([
                    'likes' => $item->likes,
                    'comments' => $item->comments,
                ]),
            ];
        });

        Metric::upsert($data->toArray(), ['data'], ['workspace_id', 'account_id', 'date']);
    }
}
