<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class SendAnnouncementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(User::ROLE_ADMIN) === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'grade_level' => filled($this->input('grade_level')) ? trim((string) $this->input('grade_level')) : null,
            'section' => filled($this->input('section')) ? trim((string) $this->input('section')) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'announcement_id' => ['required', 'integer', 'exists:announcements,id'],
            'grade_level' => ['nullable', 'string', 'max:50'],
            'section' => ['nullable', 'string', 'max:20'],
        ];
    }
}
