<?php

namespace Inovector\Mixpost;

/**
 * Registry of all available hooks in Mixpost.
 *
 * Actions — executed via HooksManager::doAction()
 * Filters — executed via HooksManager::applyFilters()
 */
final class Hooks
{
    /**
     * Actions
     */

    // Fired before a mail message is sent.
    // Callback receives: Illuminate\Notifications\Messages\MailMessage $message
    const ACTION_BEFORE_SEND_MAIL_MESSAGE = 'before_send_mail_message';

    /**
     * Filters
     */
}
