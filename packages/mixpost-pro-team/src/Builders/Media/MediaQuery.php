<?php

namespace Inovector\Mixpost\Builders\Media;

use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inovector\Mixpost\Builders\Media\Filters\MimeTypes;
use Inovector\Mixpost\Contracts\Query;
use Inovector\Mixpost\Models\Media;

class MediaQuery implements Query
{
    public static function apply(Request $request): Builder
    {
        $query = Media::query();

        if ($request->has('mime_types') && $request->get('mime_types')) {
            $query = MimeTypes::apply($query, $request->get('mime_types'));
        }

        return $query;
    }
}
