<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(User::ROLE_ADMIN) === true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'learner_id' => ['nullable', 'required_without:qr_code', 'integer', 'exists:learners,id'],
            'qr_code' => ['nullable', 'required_without:learner_id', 'string', 'max:100'],
            'session' => ['required', Rule::in(['am_in', 'am_out', 'pm_in', 'pm_out'])],
        ];
    }
}
