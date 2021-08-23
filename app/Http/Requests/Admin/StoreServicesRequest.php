<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreServicesRequest extends FormRequest
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
        return [
            'teachers.*'            => 'exists:users,id',
            'service_category_id'   => 'required|exists:service_categories,id',
            'title'                 => 'required',
            'min_capacity'          => 'nullable|numeric|min:1',
            'max_capacity'          => 'nullable|numeric|min:1|gte:min_capacity',
            'description'           => 'nullable',
            'price'                 => 'required|numeric',
            'duration'              => 'required|numeric',
            'is_online'             => 'nullable|boolean',
            'location_address'      => 'nullable|string',
            'location_phone_number' => 'nullable|string',
            'location_description'  => 'nullable|string',
            'location_latitude'     => 'nullable|string',
            'location_longitude'    => 'nullable|string',
        ];
    }
}
