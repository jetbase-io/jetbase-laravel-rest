<?php

namespace App;

use Illuminate\Http\JsonResponse;

class ApiResponse extends JsonResponse
{

    public function __construct($data = null, $code = 200, $headers = [], $options = 0)
    {
        $data = array_merge(['code' => $code], $data);
        parent::__construct($data, 200, $headers, $options);
    }

}