<?php

namespace Inovector\Mixpost\Http\Api\Controllers\Workspace;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Http\Api\Resources\AccountResource;
use Inovector\Mixpost\Models\Account;

class AccountsController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return AccountResource::collection(
            Account::latest()->get()
        );
    }

    public function show(Request $request): AccountResource
    {
        return new AccountResource(
            Account::firstOrFailByUuid($request->route('account'))
        );
    }
}
