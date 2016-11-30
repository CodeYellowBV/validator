<?php
namespace CodeYellow\Api\Test\Validation\Mock;

/**
 * A test class that creates a validator that checks if a certain
 * parameter exists in a database
 */
class ExistsValidator extends \CodeYellow\Validation\Validator
{
    protected $rules = [
        'id' => 'required|exists:users,id'
    ];
}
