<?php

return [
    /*
     * This option controls the default authentication "guard" for the Mixpost dashboard routes
     */
    'auth_guard' => env('MIXPOST_AUTH_GUARD', 'web'),

    /*
     * This is the path that Mixpost will use to load its core routes and assets.
     * You may change it if it conflicts with your other routes.
     */
    'core_path' => env('MIXPOST_CORE_PATH', 'mixpost'),

    /*
     * This option will force the callback url to use the native `mixpost` core path.
     * If you want to use custom core path, for the dashboard, but you want to use the native `mixpost` core path for the callback url,
     */
    'force_core_path_callback_to_native' => env('FORCE_CORE_PATH_CALLBACK_TO_NATIVE', false),

    /*
    * Public pages have an endpoint directly after the url domain.
    * If your application already has direct routes, you must set a prefix to avoid conflicts between routes.
    */
    'public_pages_prefix' => env('MIXPOST_PUBLIC_PAGES_PREFIX', 'pages'),

    /*
     * If you use another model for users, you can change it here.
     * It need to be extended the `Inovector\Mixpost\Abstracts\User` class.
     *
     * @deprecated This config will be removed on next major release.
     */
    'user_model' => \Inovector\Mixpost\Models\User::class,

    /*
     * Mixpost will redirect unauthorized users to the route name specified here.
     */
    'redirect_unauthorized_users_to_route' => 'mixpost.login',

    /*
     *  These middleware will be assigned to the main Mixpost routes which require authentication.
     *  You can add your own middlewares.
     */
    'middlewares' => [
        'web' => [
            'app' => [
                'web',
                \Inovector\Mixpost\Http\Base\Middleware\Bootstrap::class,
                \Inovector\Mixpost\Http\Base\Middleware\Localization::class,
            ],
            'dashboard' => [
                \Inovector\Mixpost\Http\Base\Middleware\Authenticate::class,
            ],
        ],
        'api' => [
            'api',
            \Inovector\Mixpost\Http\Api\Middleware\Bootstrap::class,
            \Inovector\Mixpost\Http\Api\Middleware\ForceJsonResponse::class,
            \Inovector\Mixpost\Http\Api\Middleware\Authenticate::class.':mixpost_access_token',
            \Inovector\Mixpost\Http\Base\Middleware\Localization::class,
        ],
    ],

    /*
     * The disk on which to store added files.
     * Choose one or more of the disks you've configured in config/filesystems.php.
     */
    'disk' => env('MIXPOST_DISK', 'public'),

    /*
     * Features status
     */
    'features' => [
        'forgot_password' => env('MIXPOST_FORGOT_PASSWORD', true),
        'two_factor_auth' => env('MIXPOST_TWO_FACTOR_AUTH', true),
        'api_access_tokens' => env('MIXPOST_API_ACCESS_TOKENS', true),
        'auto_subscribe_post_activities' => env('MIXPOST_AUTO_SUBSCRIBE_POST_ACTIVITIES', false),
    ],

    /*
     * Indicate that the uploaded file should be no more than the given number of megabytes.
     * Adding a larger file will result in an exception.
     * Values are in megabytes (MB).
     */
    'max_file_size' => [
        'image' => env('MIXPOST_MAX_IMAGE_FILE_SIZE', 15),
        'gif' => env('MIXPOST_MAX_GIF_FILE_SIZE', 15),
        'video' => env('MIXPOST_MAX_VIDEO_FILE_SIZE', 200),
    ],

    /*
     * Chunked upload configuration for large files.
     * Files larger than the threshold will be uploaded in chunks.
     * Values are in megabytes (MB).
     */
    'chunked_upload' => [
        'chunk_size' => env('MIXPOST_CHUNKED_UPLOAD_SIZE', 10),
        'threshold' => env('MIXPOST_CHUNKED_UPLOAD_THRESHOLD', 10),
    ],

    /*
     * Accepted mime types for media library upload.
     * These are all supported mime types for the image and video files. We do not guarantee that it will work with other types.
     * If you need to remove certain mime types, you are free to do so from here.
     */
    'mime_types' => [
        'image/jpg',
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/heic',
        'image/heif',
        'image/bmp',
        'video/mp4',
        'video/x-m4v',
        'video/quicktime',
        'video/x-msvideo',
        'video/x-matroska',
        'video/webm',
        'video/x-flv',
        'video/x-ms-wmv',
    ],

    /*
     * The path where to store temporary files while performing image conversions.
     * If set to null, `storage/mixpost-media/temp` will be used.
     */
    'temporary_directory_path' => null,

    /*
     * FFMPEG & FFProbe binaries paths, only used if you try to generate video thumbnails
     */
    'ffmpeg_path' => env('FFMPEG_PATH', '/usr/bin/ffmpeg'),
    'ffprobe_path' => env('FFPROBE_PATH', '/usr/bin/ffprobe'),

    /*
     * Define cache prefix
     */
    'cache_prefix' => env('MIXPOST_CACHE_PREFIX', 'mixpost'),

    /*
     * Define log channel
     * Captures connection errors with social networks or third parties used in Mixpost in a separate channel.
     * Leave blank if you want to use Laravel's default log channel
     */
    'log_channel' => env('MIXPOST_LOG_CHANNEL'),

    /*
     * The media component is integrated with third-party services Unsplash.com and Tenor.com
     * Defines the default terms for displaying media resources
     */
    'external_media_terms' => ['social', 'mix', 'content', 'popular', 'viral', 'trend', 'light', 'marketing', 'self-hosted', 'ambient', 'writer', 'technology'],

    /*
     * @deprecated This config will be removed on next major release.
     * !!! Do not change anything here !!!
     *
     * Options for each social network
     * We recommend leaving these options unchanged
     * You only change them when the API policy of the social networks changes, and you know what you are doing.
     */
    'social_provider_options' => [
        'twitter' => [
            'simultaneous_posting_on_multiple_accounts' => false,
            'post_character_limit' => 280,
            'media_limit' => [
                'photos' => 4,
                'videos' => 1,
                'gifs' => 1,
                'allow_mixing' => false,
            ],
        ],
        'facebook_page' => [
            'simultaneous_posting_on_multiple_accounts' => true,
            'post_character_limit' => 5000,
            'media_limit' => [
                'photos' => 10,
                'videos' => 1,
                'gifs' => 1,
                'allow_mixing' => false,
            ],
        ],
        'facebook_group' => [
            'simultaneous_posting_on_multiple_accounts' => true,
            'post_character_limit' => 5000,
            'media_limit' => [
                'photos' => 10,
                'videos' => 1,
                'gifs' => 1,
                'allow_mixing' => false,
            ],
        ],
        'instagram' => [
            'simultaneous_posting_on_multiple_accounts' => true,
            'post_character_limit' => 2200,
            'media_limit' => [
                'photos' => 10,
                'videos' => 1,
                'gifs' => 0,
                'allow_mixing' => true,
            ],
        ],
        'mastodon' => [
            'simultaneous_posting_on_multiple_accounts' => true,
            'post_character_limit' => 500,
            'media_limit' => [
                'photos' => 4,
                'videos' => 1,
                'gifs' => 1,
                'allow_mixing' => false,
            ],
        ],
        'youtube' => [
            'simultaneous_posting_on_multiple_accounts' => true,
            'post_character_limit' => 5000,
            'media_limit' => [
                'photos' => 0,
                'videos' => 1,
                'gifs' => 0,
                'allow_mixing' => false,
            ],
        ],
        'pinterest' => [
            'simultaneous_posting_on_multiple_accounts' => true,
            'post_character_limit' => 500,
            'media_limit' => [
                'photos' => 1,
                'videos' => 0,
                'gifs' => 0,
                'allow_mixing' => false,
            ],
        ],
        'linkedin' => [
            'simultaneous_posting_on_multiple_accounts' => false,
            'post_character_limit' => 3000,
            'media_limit' => [
                'photos' => 9,
                'videos' => 1,
                'gifs' => 1,
                'allow_mixing' => false,
            ],
        ],
        'linkedin_page' => [
            'simultaneous_posting_on_multiple_accounts' => false,
            'post_character_limit' => 3000,
            'media_limit' => [
                'photos' => 9,
                'videos' => 1,
                'gifs' => 1,
                'allow_mixing' => false,
            ],
        ],
        'tiktok' => [
            'simultaneous_posting_on_multiple_accounts' => true,
            'post_character_limit' => 2200,
            'media_limit' => [
                'photos' => 0,
                'videos' => 1,
                'gifs' => 0,
                'allow_mixing' => false,
            ],
        ],
    ],

    /*
     * The locale determines the default locale that will be used by the Mixpost
     * package to display all language strings. You are free to set this value
     * to any of the locales which will be supported by the application.
     */
    'default_locale' => env('MIXPOST_DEFAULT_LOCALE', 'en-GB'),

    /*
     * The available locales for the Mixpost
     */
    'locales' => [
        ['short' => 'ar', 'long' => 'ar-SA', 'direction' => 'rtl', 'english' => 'Arabic (Saudi Arabia)', 'native' => 'العربية (المملكة العربية السعودية)'],
        ['short' => 'ca', 'long' => 'ca-ES', 'direction' => 'ltr', 'english' => 'Catalan (Spain)', 'native' => 'Català (España)'],
        ['short' => 'cs', 'long' => 'cs-CZ', 'direction' => 'ltr', 'english' => 'Czech (Czechia)', 'native' => 'Čeština (Česko)'],
        ['short' => 'de', 'long' => 'de-DE', 'direction' => 'ltr', 'english' => 'German (Germany)', 'native' => 'Deutsch (Deutschland)'],
        ['short' => 'nl', 'long' => 'nl-NL', 'direction' => 'ltr', 'english' => 'Dutch (Netherlands)', 'native' => 'Nederlands (Nederland)'],
        ['short' => 'en', 'long' => 'en-GB', 'direction' => 'ltr', 'english' => 'English (GB)', 'native' => 'English (GB)'],
        ['short' => 'es', 'long' => 'es-ES', 'direction' => 'ltr', 'english' => 'Spanish (Spain)', 'native' => 'Español (España)'],
        ['short' => 'es', 'long' => 'es-MX', 'direction' => 'ltr', 'english' => 'Spanish (Mexico)', 'native' => 'Español (México)'],
        ['short' => 'eu', 'long' => 'eu-ES', 'direction' => 'ltr', 'english' => 'Basque (Spain)', 'native' => 'Euskara (Espainia)'],
        ['short' => 'fr', 'long' => 'fr-CA', 'direction' => 'ltr', 'english' => 'French (Canada)', 'native' => 'Français (Canada)'],
        ['short' => 'fr', 'long' => 'fr-FR', 'direction' => 'ltr', 'english' => 'French (France)', 'native' => 'Français (France)'],
        ['short' => 'it', 'long' => 'it-IT', 'direction' => 'ltr', 'english' => 'Italian (Italy)', 'native' => 'Italiano (Italia)'],
        ['short' => 'ro', 'long' => 'ro-RO', 'direction' => 'ltr', 'english' => 'Romanian (Romania)', 'native' => 'Română (Romania)'],
        ['short' => 'ru', 'long' => 'ru-RU', 'direction' => 'ltr', 'english' => 'Russian (Russia)', 'native' => 'Русский (Россия)'],
        ['short' => 'sk', 'long' => 'sk-SK', 'direction' => 'ltr', 'english' => 'Slovak (Slovakia)', 'native' => 'Slovenčina (Slovensko)'],
    ],

    /*
     * The default timezone for the Mixpost dashboard.
     * This is used for displaying dates and times in the dashboard.
     */
    'timezone' => env('MIXPOST_TIMEZONE', 'UTC'),

    /*
     * The time format for displaying times in the dashboard.
     * Use 12 for 12-hour format (AM/PM) or 24 for 24-hour format.
     */
    'time_format' => env('MIXPOST_TIME_FORMAT', 12),

    /*
     * The first day of the week for calendar displays.
     * Use 0 for Sunday, 1 for Monday, 2 for Tuesday, etc.
     */
    'week_starts_on' => env('MIXPOST_FIRST_DAY_WEEK', 1),
];
