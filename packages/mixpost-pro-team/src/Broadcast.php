<?php

namespace Inovector\Mixpost;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class Broadcast
{
    public static function driver()
    {
        return Config::get('broadcasting.default');
    }

    public static function echoOptions(): ?array
    {
        return match (self::driver()) {
            'reverb' => self::reverbEchoOptions(),
            'pusher' => self::pusherEchoOptions(),
            default => null,
        };
    }

    private static function reverbEchoOptions(): array
    {
        return [
            'broadcaster' => 'reverb',
            'key' => Config::get('reverb.apps.apps.0.key'),
            'wsHost' => Config::get('reverb.apps.apps.0.options.host'),
            'wsPort' => Config::get('reverb.apps.apps.0.options.port'),
            'wssPort' => Config::get('reverb.apps.apps.0.options.port'),
            'forceTLS' => Config::get('reverb.apps.apps.0.options.scheme') === 'https',
            'encrypted' => Config::get('reverb.apps.apps.0.options.scheme') === 'https',
            'enabledTransports' => ['ws', 'wss'],
            'disableStats' => true,
        ];
    }

    private static function pusherEchoOptions(): array
    {
        $key = Config::get('broadcasting.connections.pusher.key');
        $host = Config::get('broadcasting.connections.pusher.options.host');
        $port = Config::get('broadcasting.connections.pusher.options.port');
        $cluster = Str::of($host)->before('.pusher.com')->afterLast('-')->toString() ?: 'mt1';
        $scheme = Config::get('broadcasting.connections.pusher.options.scheme', 'https');

        $options = [
            'broadcaster' => 'pusher',
            'key' => $key,
            'cluster' => $cluster,
            'encrypted' => true,
            'forceTLS' => true,
        ];

        // Add custom host and port if set
        if (! Str::of($host)->endsWith('.pusher.com') && $port) {
            $options['wsHost'] = $host;
            $options['wsPort'] = $port;
            $options['wssPort'] = $port;
            $options['forceTLS'] = $scheme === 'https';
            $options['encrypted'] = $scheme === 'https';
            $options['enabledTransports'] = ['ws', 'wss'];
            $options['disableStats'] = true;
        }

        return $options;
    }

    public static function routes(): void
    {
        require __DIR__.'/../routes/broadcast/channels.php';
    }
}
