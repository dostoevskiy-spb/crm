<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CreateIndividualRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|min:1|max:20',
            'last_name' => 'required|string|min:1|max:20',
            'middle_name' => 'required|string|min:1|max:20',
            'status_id' => 'required|integer|min:1',
            'position_id' => 'nullable|integer|min:1',
            'login' => 'nullable|string|min:6',
            'is_company_employee' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Имя обязательно для заполнения',
            'first_name.min' => 'Имя должно содержать минимум 1 символ',
            'first_name.max' => 'Имя не может содержать более 20 символов',
            'last_name.required' => 'Фамилия обязательна для заполнения',
            'last_name.min' => 'Фамилия должна содержать минимум 1 символ',
            'last_name.max' => 'Фамилия не может содержать более 20 символов',
            'middle_name.required' => 'Отчество обязательно для заполнения',
            'middle_name.min' => 'Отчество должно содержать минимум 1 символ',
            'middle_name.max' => 'Отчество не может содержать более 20 символов',
            'status_id.required' => 'Статус обязателен для заполнения',
            'status_id.integer' => 'Статус должен быть числом',
            'status_id.min' => 'Некорректный статус',
            'position_id.integer' => 'Должность должна быть числом',
            'position_id.min' => 'Некорректная должность',
            'login.min' => 'Логин должен содержать минимум 6 символов',
            'is_company_employee.boolean' => 'Поле "Сотрудник компании" должно быть true или false',
        ];
    }
}
