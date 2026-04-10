<?php

namespace Inovector\Mixpost\Http\Base\Requests\Workspace\AI;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Inovector\Mixpost\Configs\AIConfig;
use Inovector\Mixpost\Events\AI\AITextGenerated;
use Inovector\Mixpost\Facades\AIManager;
use Inovector\Mixpost\Responses\AIProviderResponse;

class AIGenerateText extends FormRequest
{
    public AIProviderResponse $response;

    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'max:1000'],
            'tone' => ['required', Rule::in(['neutral', 'friendly', 'formal', 'edgy', 'engaging'])],
            'character_limit' => ['required', 'integer', 'min:1', 'max:1000'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $this->generateResponse();

            if ($this->response->hasError()) {
                $validator->errors()->add('ai_error', $this->response->context);
            }
        });
    }

    public function handle(): ?string
    {
        return $this->response->firstChoice()?->messageContent;
    }

    private function generateResponse(): void
    {
        $agentInstructions = app(AIConfig::class)->get('instructions');
        $characterLimit = "Ensure that the reply must not exceed the limit of {$this->input('character_limit')} characters.";
        $languageOutputInstructions = 'Ensure that the reply should be in user language.';

        $this->response = AIManager::connect()->generateText(
            prompt: strip_tags($this->input('prompt')),
            instructions: implode("\n\n", [$agentInstructions, $characterLimit, $languageOutputInstructions])
        );

        AITextGenerated::dispatch($this->response);
    }
}
