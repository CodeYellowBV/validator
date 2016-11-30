<?php
namespace CodeYellow\Api\Test\Validation\Mock;

/**
 * A test class that creates a validator that is needed for
 * simple testing of the custom validator
 */
class SimpleValidator extends \CodeYellow\Validation\Validator
{
    protected $rules = [
        'age' => 'required|numeric|min:5',
        'size' => 'numeric|min:10'
    ];
}
