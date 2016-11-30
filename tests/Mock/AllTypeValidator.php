<?php
namespace CodeYellow\Api\Test\Validation\Mock;

/**
 * A test class that creates a validator that contains a rule
 * for each datatype. Is needed to ensure that validateInteger,
 * ValidateBoolean and validateNumeric methods work
 */
class AllTypeValidator extends \CodeYellow\Validation\Validator
{
    protected $rules = [
        'numeric' => 'numeric',
        'integer' => 'integer',
        'boolean' => 'boolean',
        'string' => 'string'
    ];
}
