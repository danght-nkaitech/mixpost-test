<?php

namespace Inovector\MixpostEnterprise\Configs;

use Inovector\Mixpost\Abstracts\Config;

class SystemConfig extends Config
{
    public function group(): string
    {
        return 'system';
    }

    public function form(): array
    {
        return [
            'multiple_workspaces' => false,
            'workspace_twitter_service' => false,
            'twitter_api_workspace_docs_url' => '',
        ];
    }

    public function rules(): array
    {
        return [
            'multiple_workspaces' => ['required', 'boolean'],
            'workspace_twitter_service' => ['required', 'boolean'],
            'twitter_api_workspace_docs_url' => ['nullable', 'url'],
        ];
    }

    public function messages(): array
    {
        return [];
    }

    public function multipleWorkspacesEnabled(): bool
    {
        return (bool) $this->get('multiple_workspaces');
    }

    public function allowWorkspaceTwitterService(): bool
    {
        return (bool) $this->get('workspace_twitter_service');
    }
}
