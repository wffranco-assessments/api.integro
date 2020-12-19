<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMovieRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $year = date('Y');
        return [
            'title' => 'required',
            'synopsis' => 'max:65535',
            'year' => "required|numeric|max:{$year}",
        ];
    }

    public function messages()
    {
        return [
        ];
    }
}
