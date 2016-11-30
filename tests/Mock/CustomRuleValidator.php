<?php
namespace CodeYellow\Api\Test\Validation\Mock;

/**
 * A test class that creates a validator that is needed for testing
 * custom ruless
 */
class CustomRuleValidator extends \CodeYellow\Validation\Validator
{
    protected $rules = [
        'age' => 'required|isOdd'
    ];

    protected $messages = [
        'age' => 'Age out of bound'
    ];

    public function validateIsOdd($attribute, $value, $parameters)
    {
        return $value % 2 == 1;
    }
}
