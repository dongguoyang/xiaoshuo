<?php

namespace App\Logics\Traits;

use Illuminate\Contracts\Validation\Factory;

trait ValidateTrait
{
    public function validate($input, array $rules, array $messages = [])
    {
        $validator = $this->getValidationFactory()->make($input, $rules, $messages);
        if ($validator->fails()) {
            return $validator->errors()->all();
        }
        return true;
    }

    protected function getValidationFactory()
    {
        return app(Factory::class);
    }
}