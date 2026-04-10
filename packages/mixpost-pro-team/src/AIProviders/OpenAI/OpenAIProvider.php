<?php

namespace Inovector\Mixpost\AIProviders\OpenAI;

use Illuminate\Support\Arr;
use Inovector\Mixpost\Abstracts\AIProvider;
use Inovector\Mixpost\Data\AIChoiceData;
use Inovector\Mixpost\Enums\AIProviderResponseStatus;
use Inovector\Mixpost\Responses\AIProviderResponse;
use Inovector\Mixpost\Services\OpenAIService;
use OpenAI;
use OpenAI\Client as OpenAIClient;

class OpenAIProvider extends AIProvider
{
    public static function name(): string
    {
        return 'openai';
    }

    public static function nameLocalized(): string
    {
        return 'OpenAI';
    }

    public static function service(): string
    {
        return OpenAIService::class;
    }

    public function generateText(string $prompt, string $instructions = ''): AIProviderResponse
    {
        $result = $this->client()->chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                ['role' => 'system', 'content' => $instructions],
                ['role' => 'user', 'content' => $prompt],
            ],
        ]);

        return AIProviderResponse::withStatus(AIProviderResponseStatus::OK)->withChoices(Arr::map($result->choices, function ($choice) {
            return AIChoiceData::from(index: $choice->index, messageContent: $choice->message->content);
        }));
    }

    public function generateImage(): AIProviderResponse
    {
        return AIProviderResponse::withStatus(AIProviderResponseStatus::OK);
    }

    protected function client(): OpenAIClient
    {
        return OpenAI::client($this->getServiceConfiguration('secret_key'));
    }
}
