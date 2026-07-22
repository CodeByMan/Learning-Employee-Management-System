<?php

namespace App\Http\Requests\Admin;

use App\Models\Learner;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLearnerRequest extends FormRequest
{
    public function authorize(): bool
    {
        $learner = $this->route('learner');

        return $learner instanceof Learner
            && $this->user()?->can('update', $learner) === true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'fname' => trim((string) $this->input('fname')),
            'mname' => filled($this->input('mname')) ? trim((string) $this->input('mname')) : null,
            'lname' => trim((string) $this->input('lname')),
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'grade_level' => trim((string) $this->input('grade_level')),
            'section' => trim((string) $this->input('section')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Learner $learner */
        $learner = $this->route('learner');

        return [
            'fname' => ['required', 'string', 'max:100'],
            'mname' => ['nullable', 'string', 'max:100'],
            'lname' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email:rfc',
                'max:255',
                Rule::unique(Learner::class, 'email')->ignore($learner),
            ],
            'grade_level' => ['required', 'string', 'max:50'],
            'section' => ['required', 'string', 'max:20'],
        ];
    }
}
