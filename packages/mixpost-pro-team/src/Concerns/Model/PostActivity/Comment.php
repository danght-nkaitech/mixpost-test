<?php

namespace Inovector\Mixpost\Concerns\Model\PostActivity;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Inovector\Mixpost\Abstracts\User;
use Inovector\Mixpost\Concerns\UsesUserModel;
use Inovector\Mixpost\Enums\PostActivityType;
use Inovector\Mixpost\Enums\PostCommentReactionToggleType;
use Inovector\Mixpost\Models\PostActivityReaction;
use LogicException;

trait Comment
{
    use UsesUserModel;

    public function reactions(): HasMany
    {
        return $this->hasMany(PostActivityReaction::class, 'activity_id');
    }

    public function isComment(): bool
    {
        return $this->type === PostActivityType::COMMENT;
    }

    public function toggleReaction(int|User $user, string $reaction): PostCommentReactionToggleType
    {
        if ($this->type !== PostActivityType::COMMENT) {
            throw new LogicException('The type must be a comment.');
        }

        $userId = $user instanceof User ? $user->id : $user;

        $record = $this->reactions()
            ->where('user_id', $userId)
            ->where('reaction', $reaction)
            ->first();

        if (! $record) {
            $this->reactions()->create([
                'reaction' => $reaction,
                'user_id' => $userId,
            ]);

            return PostCommentReactionToggleType::CREATED;
        }

        $record->delete();

        return PostCommentReactionToggleType::DELETED;
    }

    public function groupedReactions()
    {
        return $this->reactions()
            ->with('user')
            ->get()
            ->groupBy('reaction')
            ->map(function ($reactions, $reaction) {
                return [
                    'reaction' => $reaction,
                    'users' => $reactions->pluck('user')->filter()->map(fn ($user) => $user->only('id', 'name')),
                    'count' => $reactions->count(),
                ];
            })
            ->values();
    }

    public function getMentioned(): Collection
    {
        $text = $this->text;

        preg_match_all('/data-type="mention" data-id="([^"]+)"/', $text, $matches);
        $mentionedIds = $matches[1];

        return $this->post?->workspace?->users()->whereIn('user_id', array_unique($mentionedIds))->get() ?? collect();
    }
}
