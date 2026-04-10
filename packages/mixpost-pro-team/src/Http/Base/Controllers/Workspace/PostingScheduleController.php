<?php

namespace Inovector\Mixpost\Http\Base\Controllers\Workspace;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use Inertia\Inertia;
use Inertia\Response;
use Inovector\Mixpost\Http\Base\Requests\Workspace\UpdatePostingSchedule;
use Inovector\Mixpost\PostingSchedule;

class PostingScheduleController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Workspace/PostingSchedule', [
            'times' => fn () => PostingSchedule::timesUserTimezone(),
        ]);
    }

    public function update(UpdatePostingSchedule $updatePostingSchedule): RedirectResponse
    {
        $updatePostingSchedule->handle();

        return back();
    }
}
