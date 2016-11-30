<?php


namespace CodeYellow\Api\Test\Validation\Mock;


class NestedCollectionValidator extends \CodeYellow\Validation\Validator
{
    protected $validators = [
        'simple' => 'CodeYellow\Api\Test\Validation\Mock\SimpleValidator'
    ];

    protected $rules = [
        'nested' => 'array|nested_collection:simple'
    ];
}
