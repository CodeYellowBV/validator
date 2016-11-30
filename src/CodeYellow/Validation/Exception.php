<?php
namespace CodeYellow\Validation;

use \Illuminate\Validation\ValidationException;

/**
 * A custom exception that extends the validation exception
 * from Laravel, such that the validator can still be accessed.
 * This is needed if we want to display the custom api messages
 * from the validator.
 */
class Exception extends ValidationException
{
    public $validator;

    /**
     * Construct a new Exception. Will save the validator
     * for later use.
     *
     * @param Validator $val CodeYellow\Api\Validation\Validator
     * the validator that triggered this exception.
     */
    public function __construct(
        Validator $val
    ) {
        $this->validator = $val;
        parent::__construct($this->validator->getMessageBag());


        // overwrite message for debug purposes
        $this->message = implode(";", $this->validator->getMessageBag()->all());
    }

    /**
     * Get the validator that triggered this error.
     *
     * @return \CodeYellow\Validation\Validator Validator that
     * triggered the exception.
     */
    public function getValidator()
    {
        return $this->validator;
    }
}
