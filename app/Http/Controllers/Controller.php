<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected function formatValidationErrors(\Illuminate\Validation\Validator $validator)
    {
        abort(500, 'Not supported');
        return error([
            'error' => collect($validator->errors()->all())->implode(' '),
            'validator' => $validator->errors()->all(),
        ]);
    }
}
