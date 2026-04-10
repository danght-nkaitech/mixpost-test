<?php

namespace Inovector\Mixpost\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Inovector\Mixpost\Concerns\Mail;
use Inovector\Mixpost\Contracts\QueueWorkspaceAware;
use Inovector\Mixpost\Models\PostActivity;
use Inovector\Mixpost\Util;

class NewPostActivity extends Notification implements QueueWorkspaceAware, ShouldQueue
{
    use Mail, Queueable, SerializesModels;

    public $deleteWhenMissingModels = true;

    public function __construct(public readonly PostActivity $activity) {}

    public function shouldSend(object $notifiable, string $channel): bool
    {
        if (! $post = $this->activity->post) {
            return false;
        }

        if (! $post->workspace) {
            return false;
        }

        return true;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $type = $this->activity->isComment() ? 'new_comment' : 'new_activity';

        [$actionUrl, $actionText] = $this->getAction();

        return $this->mailMessage()
            ->subject($this->getSubject())
            ->markdown('mixpost::mail.newPostActivity', [
                'notifiable' => $notifiable,
                'activity' => $this->activity,
                'type' => $type,
                'author' => $this->getAuthor(),
                'actionUrl' => $actionUrl,
                'actionText' => $actionText,
            ]);
    }

    protected function getSubject(): string
    {
        $default = __('mixpost::post_activity.new_post_activity').' ['.Str::limit($this->activity->post->uuid, 8).']';

        $postContent = $this->activity->post->versions()
            ->original()
            ->first()
            ?->content ?? [];

        $body = Util::removeHtmlTags(
            Arr::get($postContent, '0.body', $default)
        ) ?: $default;

        return Str::limit($body, 150);
    }

    protected function getAuthor(): string
    {
        if (! $this->activity->user) {
            return __('mixpost::system.system');
        }

        return $this->activity->user?->name ?? __('mixpost::user.user_deleted');
    }

    protected function getAction(): array
    {
        return [
            route('mixpost.posts.edit', ['workspace' => $this->activity->post->workspace->uuid, 'post' => $this->activity->post->uuid]), // action url
            __('mixpost::post.view'), // action text
        ];
    }
}
