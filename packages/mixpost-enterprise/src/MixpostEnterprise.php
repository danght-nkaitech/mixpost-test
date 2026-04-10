<?php

namespace Inovector\MixpostEnterprise;

use Illuminate\Support\HtmlString;

class MixpostEnterprise
{
    public static function assets()
    {
        $hot = __DIR__.'/../resources/dist/hot';

        $devServerIsRunning = file_exists($hot);

        if ($devServerIsRunning) {
            $viteServer = file_get_contents($hot);

            return new HtmlString(<<<HTML
                <script type="module" src="$viteServer/@vite/client"></script>
                <script type="module" src="$viteServer/resources/js/app.js"></script>
            HTML
            );
        }

        $manifestPath = public_path('vendor/mixpost-enterprise/manifest.json');

        if (! file_exists($manifestPath)) {
            return new HtmlString(<<<'HTML'
                <div>The manifest.json file could not be found.</div>
            HTML
            );
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);

        return new HtmlString(<<<HTML
                <script type="module" src="/vendor/mixpost-enterprise/{$manifest['resources/js/app.js']['file']}"></script>
                <link rel="stylesheet" href="/vendor/mixpost-enterprise/{$manifest['resources/js/app.js']['css'][0]}">
            HTML
        );
    }
}
