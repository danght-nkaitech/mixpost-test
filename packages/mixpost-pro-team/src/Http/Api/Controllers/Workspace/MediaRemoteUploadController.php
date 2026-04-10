<?php

namespace Inovector\Mixpost\Http\Api\Controllers\Workspace;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Inovector\Mixpost\Enums\RemoteMediaDownloadStatus;
use Inovector\Mixpost\Facades\WorkspaceManager;
use Inovector\Mixpost\Http\Api\Requests\Workspace\MediaRemoteUpload;
use Inovector\Mixpost\Http\Api\Resources\MediaResource;
use Inovector\Mixpost\Models\Media;
use Inovector\Mixpost\Support\RemoteMediaDownloadTracker;

class MediaRemoteUploadController extends Controller
{
    public function initiate(MediaRemoteUpload $request): JsonResponse
    {
        return response()->json($request->handle());
    }

    public function status(Request $request): JsonResponse
    {
        $downloadId = $request->route('downloadId');

        $data = RemoteMediaDownloadTracker::get($downloadId);

        if (! $data) {
            return response()->json([
                'status' => RemoteMediaDownloadStatus::FAILED->value,
                'error' => __('mixpost::rules.remote_file.download_not_found'),
            ], 404);
        }

        $workspaceId = WorkspaceManager::current()->id;

        if ($data['workspace_id'] !== $workspaceId) {
            return response()->json([
                'status' => RemoteMediaDownloadStatus::FAILED->value,
                'error' => __('mixpost::rules.remote_file.download_not_found'),
            ], 404);
        }

        $response = [
            'status' => $data['status'],
            'progress' => $data['progress'],
        ];

        if ($data['status'] === RemoteMediaDownloadStatus::COMPLETED->value && $data['media_id']) {
            $media = Media::find($data['media_id']);

            if ($media) {
                $response['media'] = new MediaResource($media);
            }

            RemoteMediaDownloadTracker::delete($downloadId);
        }

        if ($data['status'] === RemoteMediaDownloadStatus::FAILED->value) {
            $response['error'] = $data['error'];

            RemoteMediaDownloadTracker::delete($downloadId);
        }

        return response()->json($response);
    }
}
