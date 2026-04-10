<?php

namespace Inovector\Mixpost\Reports;

use Inovector\Mixpost\Abstracts\Report;
use Inovector\Mixpost\Models\Account;

class LinkedinReports extends Report
{
    public function __invoke(Account $account, string $period): array
    {
        return [
            'metrics' => [],
            'audience' => [],
        ];
    }
}
