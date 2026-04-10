<?php

namespace Inovector\Mixpost\Enums;

enum RemoteMediaDownloadStatus: string
{
    case PENDING = 'pending';
    case DOWNLOADING = 'downloading';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}
