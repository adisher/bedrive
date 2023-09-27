<?php namespace Common\Auth\Requests;

use Common\Core\BaseFormRequest;

class ModifyUsers extends BaseFormRequest
{
    public function rules(): array
    {
        $except = $this->getMethod() === 'PUT' ? $this->route('user')->id : '';

        $rules = [
            // alpha and space/dash
            'first_name' =>
                'string|min:2|max:255|nullable|regex:/^[\pL\s\-]+$/u',
            'last_name' =>
                'string|min:2|max:255|nullable|regex:/^[\pL\s\-]+$/u',
            'permissions' => 'array',
            'roles' => 'array',
            'roles.*' => 'int',
            'password' => 'min:3|max:255',
            'email' => "email|min:3|max:255|unique:users,email,$except",
            'available_space' => 'nullable|min:0',
        ];

        if ($this->method() === 'POST') {
            $rules['email'] = 'required|' . $rules['email'];
            $rules['password'] = 'required|' . $rules['password'];
        }

        return $rules;
    }
}
