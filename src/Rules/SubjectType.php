<?php

namespace dnj\UserLogger\Rules;

use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Database\Eloquent\Model;

class SubjectType implements InvokableRule
{
    public function __invoke($attribute, $value, $fail)
    {
        if (!is_string($value) or !is_subclass_of($value, Model::class)) {
            $fail('validation.subject-type')->translate();

            return;
        }
    }
}
