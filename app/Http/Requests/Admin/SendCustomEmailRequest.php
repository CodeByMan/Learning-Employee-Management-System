<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class SendCustomEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(User::ROLE_ADMIN) === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'subject' => trim((string) $this->input('subject')),
            'content' => trim((string) $this->input('content')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'subject' => ['required', 'string', 'max:255', 'not_regex:/[\r\n]/'],
            'content' => ['required', 'string', 'max:10000'],
            'recipients' => ['required', 'array', 'min:1', 'max:25'],
            'recipients.*' => ['integer', 'distinct', 'exists:users,id'],
        ];
    }
}
