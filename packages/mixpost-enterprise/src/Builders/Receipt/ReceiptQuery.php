<?php

namespace Inovector\MixpostEnterprise\Builders\Receipt;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inovector\Mixpost\Contracts\Query;
use Inovector\MixpostEnterprise\Builders\Receipt\Filters\InvoiceNumber;
use Inovector\MixpostEnterprise\Builders\Receipt\Filters\WorkspaceUuid;
use Inovector\MixpostEnterprise\Models\Receipt;

class ReceiptQuery implements Query
{
    public static function apply(Request $request): Builder
    {
        $query = Receipt::query();

        if ($request->has('workspace_uuid') && $request->get('workspace_uuid')) {
            $query = WorkspaceUuid::apply($query, $request->get('workspace_uuid'));
        }

        if ($request->has('invoice_number') && $request->get('invoice_number')) {
            $query = InvoiceNumber::apply($query, $request->get('invoice_number'));
        }

        return $query;
    }
}
