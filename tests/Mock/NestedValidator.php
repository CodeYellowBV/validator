<?php


namespace CodeYellow\Api\Test\Validation\Mock;


class NestedValidator extends \CodeYellow\Validation\Validator
{
    protected $validators = [
        'simple' => 'CodeYellow\Api\Test\Validation\Mock\SimpleValidator'
    ];

    protected $rules = [
        'nested' => 'required|array|nested:simple'
    ];
}
