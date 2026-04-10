<?php

namespace Inovector\Mixpost\Support;

use Inovector\Mixpost\Models\Media;

class PostVersionHelpers
{
    /**
     * @param  array  $videoThumbsRelationships  is supposed to represent the video_thumbs array from mixpost_post_versions->content
     */
    public static function getThumbForMediaId(int $mediaId, array $videoThumbsRelationships): ?Media
    {
        $thumbId = self::getThumbIdForMediaId($mediaId, $videoThumbsRelationships);

        return $thumbId ? Media::find($thumbId) : null;
    }

    public static function getThumbIdForMediaId(int $mediaId, array $videoThumbsRelationships): ?int
    {
        foreach ($videoThumbsRelationships as $videoThumbsRelationship) {
            if (isset($videoThumbsRelationship['media_id']) && isset($videoThumbsRelationship['thumb_id'])) {
                if ($videoThumbsRelationship['media_id'] == $mediaId) {
                    return $videoThumbsRelationship['thumb_id'];
                }
            }
        }

        return null;
    }
}
