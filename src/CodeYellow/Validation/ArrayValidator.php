<?php
namespace CodeYellow\Validation;

use \Symfony\Component\Translation\TranslatorInterface;
use \Illuminate\Support\MessageBag;

/**
 * ArrayValidator class. Is a mix of a validator, and the arr::get
 * Is made for validation of values in a validation class (for example)
 * in searches.
 */
class ArrayValidator
{
    private $validator;

    /**
     * Construct a new arrayValidator object.
     *
     * @param TranslatorInterface $translators
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Verifies that a key in params satisfies the given rules.
     *
     * @param array $params The array that might contain the key.
     * @param string $key The key that we search for.
     * @param string $rules The rules that the key needs to satisfy.
     * @param Illuminate\Support\MessageBag $messages MessageBag to
     * which messages are added if the verify fails.
     * @return boolean Does $key in $param satisfy $rules?
     */
    public function verify(array $params, $key, $rules, MessageBag &$messages = null)
    {
        // Create new validator object that is the same as the parent object.
        $validator = clone $this->validator;
        $validator->setRules([$key => $rules]); // Set the correct rules

        // Checks if key is set. If it is set, verify only this key
        // else verify the empty array (will only scream if required is set).
        $validatorArray = array_key_exists($key, $params) ? [$key => $params[$key]] : [];
        $isValid = $validator->verify($validatorArray);

        // Append messages to the provided messagebag. Make a new
        // bag if no bag is provided.
        $newMessages = $validator->getMessageBag();
        if (is_null($messages)) {
            $messages = $newMessages;
        } else {
            $messages->merge($newMessages);
        }

        return $isValid;

    }

    /**
     * Returns and parses a value from an array, if it satisfies the rules
     * throws an exception if they key does not abide to the rules.
     *
     * N.b. messagebag not needed here, since it can be fetched from the thrown
     * exception if applicable.
     *
     * @param array $params The array that might contain the key.
     * @param string $key The key that we search for.
     * @param string $rules The rules that the key needs to satisfy.
     * @throws \CodeYellow\Api\Validation\ValidationException
     * @return string $params[$key] parsed
     */
    public function get(array $params, $key, $rules)
    {
        $validator = clone $this->validator;
        $validator->setRules([$key => $rules]);

        // Checks if key is set. If it is set, verify only this key.
        // Else, verify the empty array (will only scream if required is set).
        $validatorArray = isset($params[$key]) ? [$key => $params[$key]] : [];

        $validator->parse($validatorArray);

        return isset($validatorArray[$key]) ? $validatorArray[$key] : null;
    }
}
