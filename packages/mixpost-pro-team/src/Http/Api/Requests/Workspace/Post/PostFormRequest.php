<?php

namespace Inovector\Mixpost\Http\Api\Requests\Workspace\Post;

use Illuminate\Support\Arr;
use Inovector\Mixpost\Http\Base\Requests\Workspace\Post\PostFormRequest as BasePostForm;

abstract class PostFormRequest extends BasePostForm
{
    public function rules(): array
    {
        return array_merge(parent::rules(), [
            'timezone' => ['sometimes', 'timezone'],
            'versions.*.account_id' => ['required', 'int', function ($attribute, $value, $fail) {
                if ($value == 0) {
                    return;
                }

                if (! in_array($value, $this->inputAccounts())) {
                    $fail('The account_id must be in the accounts field.');
                }
            }],
            'schedule' => ['sometimes', 'boolean', function ($attribute, $value, $fail) {
                if ($value && empty($this->inputAccounts())) {
                    $fail('The accounts field must not be empty when schedule is set.');
                }
            }],
            'schedule_now' => ['sometimes', 'boolean', function ($attribute, $value, $fail) {
                if ($value && empty($this->inputAccounts())) {
                    $fail('The accounts field must not be empty when schedule_now is set.');
                }
            }],
            'queue' => ['sometimes', 'boolean', function ($attribute, $value, $fail) {
                if ($value && empty($this->inputAccounts())) {
                    $fail('The accounts field must not be empty when queue is set.');
                }
            }],
        ]);
    }

    private function inputAccounts(): array
    {
        return Arr::wrap($this->input('accounts', []));
    }
}
