<?php
namespace CodeYellow\Validation;

use \Symfony\Component\Translation\TranslatorInterface;
use \Illuminate\Validation\PresenceVerifierInterface;
use \Illuminate\Contracts\Container\Container;
use Illuminate\Support\Arr;

/**
 * Validator class, extends the illuminate validator
 * Makes the validator class not need a factory and
 * makes it extendable/injectable.
 */
class Validator extends \Illuminate\Validation\Validator
{
    /**
     * An array of possible sub validators to use
     * @var array
     */
    protected $validators = [];

    /**
     * Dependency Injection container
     *
     * @var Container
     */
    protected $container;

    /**
     * Construct a Validator, and set all the data to the predefined data
     * Also, sets data to an empty array.
     *
     * @param TranslatorInterface $translator Translator used to give messages.
     * @param PresenceVerifierInterface $presenceVerifier
     */
    public function __construct(
        TranslatorInterface $translator,
        PresenceVerifierInterface $presenceVerifier,
        Container $container
    ) {
        is_array($this->rules) || $this->rules = [];
        is_array($this->messages) || $this->messages = [];
        is_array($this->customAttributes) || $this->customAttributes = [];
        parent::__construct($translator, [], $this->rules, $this->messages, $this->customAttributes);
        $this->setPresenceVerifier($presenceVerifier);
        $this->container = $container;
        // See https://github.com/laravel/framework/commit/980d098ad091a5087a93202ebd4c091e336f3e58
        $this->implicitRules = array_merge($this->implicitRules, ['Array', 'Boolean', 'Integer', 'Numeric', 'String']);
    }

    /**
     * Validates $params against the predefined rules.
     *
     * @param array $params Parameters to be verified.
     * @return boolean Is $params valid.
     */
    public function verify(array $params)
    {
        // Parse the data.
        $this->setData($params);

        // And check if it passes.
        return parent::passes();
    }

    /**
     * Parses $params to satisfy the validator.
     *
     * @param array &$params Parameter to be parsed.
     * @throws \CodeYellow\Validation\ValidationException
     * @return array $params After validation.
     */
    public function parse(array &$params)
    {
        if (!$this->verify($params)) {
            throw new Exception($this);
        }

        // Filter out all data is not in the validator.
        $params = array_intersect_key($this->getData(), $this->rules);
        return $params;
    }


    /* The following methods are overridden to ensure that data casting is done
     * i.e. if you have pass '14' to an integer, it will be 14 afterwards.
     */

    /**
     * Validate that an attribute is a boolean.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateBoolean($attribute, $value)
    {
        if (! Arr::has($this->data, $attribute)) {
            return true;
        }

        if (is_null($value)){
            unset($this->data['attribute']);
            return true;
        }

        if (parent::validateBoolean($attribute, $value)) {
            $this->data[$attribute] = (boolean) $value;
            return true;
        }
        return false;
    }

    /**
     * Validate that an attribute is a string.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateString($attribute, $value)
    {
        if (! Arr::has($this->data, $attribute)) {
            return true;
        }


        if (is_null($value)){
            unset($this->data[$attribute]);
            return true;
        }

        return parent::validateString($attribute, $value);
    }

    /**
     * Validate that an attribute is an integer.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateInteger($attribute, $value)
    {
        if (! Arr::has($this->data, $attribute)) {
            return true;
        }

        if (is_null($value)){
            unset($this->data[$attribute]);
            return true;
        }

        if (parent::validateInteger($attribute, $value)) {
            $this->data[$attribute] = (int) $value;
            return true;
        }
        return false;
    }

    /**
     * Validate that an attribute is numeric.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateNumeric($attribute, $value)
    {
        if (! Arr::has($this->data, $attribute)) {
            return true;
        }

        if (is_null($value)){
            unset($this->data[$attribute]);
            return true;
        }

        if (parent::validateNumeric($attribute, $value)) {
            // If you add 0 to a string, it will be converted
            // to a numeric.
            $this->data[$attribute] += 0;
            return true;
        }
        return false;
    }

    /**
     * Validate that an attribute is an array.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    protected function validateArray($attribute, $value)
    {
        if (! Arr::has($this->data, $attribute)) {
            return true;
        }

        if (is_null($value)){
            unset($this->data[$attribute]);
            return true;
        }
        // Do not call parent, because NULL IS NOT A VALID array.
        return parent::validateArray($attribute, $value);
    }

    /**
     * Validate a validator.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @param  mixed   $parameters
     * @return bool
     */
    public function validateValidator($attribute, $value, $parameters)
    {
        foreach ($parameters as $validatorClass) {
            $validator = $this->container->make($validatorClass);

            foreach ($value as &$phonenumber) {
                $phonenumber = $validator->parse($phonenumber);
            }
        }
        return true;
    }

    /**
     * Allows to nest items in a validator
     *
     * @param $attribute
     * @param $value
     * @param $parameter
     * @return bool
     */
    public function validateNested($attribute, $value, $parameter)
    {
        if (!isset($parameter[0])) {
            throw new \InvalidArgumentException('No validator given for nested validation');
        }

        if (!array_key_exists($parameter[0], $this->validators)) {
            throw new \InvalidArgumentException('No such validator found:' .  $parameter[0]);
        }
        $className = $this->validators[$parameter[0]];

        if ($className instanceof Validator) {
            $validator = $className;
        } else {
            if (!class_exists($className)) {
                throw new \InvalidArgumentException('Can\'t find the validator ' . $className);
            }

            $validator = $this->container->make($className);
        }

        if (! ($validator instanceof Validator)) {
            throw new \InvalidArgumentException('This is not a validator you stupid!: ' . $className);
        }

        if (!$validator->verify($value)) {
            // Update the failures that occur
            $failed = $validator->failed();
            foreach ($failed as $subAttribute => $error) {
                $this->failedRules[$attribute . '.' . $subAttribute] = $error;
            }

            foreach ($validator->getMessageBag()->getMessages() as $key => $messages) {
                foreach ($messages as $message) {
                    $this->getMessageBag()->add($attribute . '.' . $key, $message);
                }
            }
        }


        // The validator always passes, because it is not a validator, but rather combines the result of other validators.
        // In this case the message that this validator failed is not displayed. Rather the messages of the rules that
        // failed are displayed. This is also used to determine if the whole model is valid, so this is ok.
        return true;
    }

    /**
     * Allows to nest items in a validator
     *
     * @param $attribute
     * @param $value
     * @param $parameter
     * @return bool
     */
    public function validateNestedCollection($attribute, $value, $parameter)
    {
        if (!isset($parameter[0])) {
            throw new \InvalidArgumentException('No validator given for nested validation');
        }

        if (!array_key_exists($parameter[0], $this->validators)) {
            throw new \InvalidArgumentException('No such validator found:' .  $parameter[0]);
        }
        $className = $this->validators[$parameter[0]];

        if ($className instanceof Validator) {
            $validator = $className;
        } else {
            if (!class_exists($className)) {
                throw new \InvalidArgumentException('Can\'t find the validator ' . $className);
            }

            $validator = $this->container->make($className);
        }

        if (! ($validator instanceof Validator)) {
            throw new \InvalidArgumentException('This is not a validator you stupid!: ' . $className);
        }


        $counter = 0;
        foreach ($value as $item) {
            if (!$validator->verify($item)) {
                // Update the failures that occur
                $failed = $validator->failed();
                foreach ($failed as $subAttribute => $error) {
                    $this->failedRules[$attribute . '.' . $counter . '.' . $subAttribute] = $error;
                }

                foreach ($validator->getMessageBag()->getMessages() as $key => $messages) {
                    foreach ($messages as $message) {
                        $this->getMessageBag()->add($attribute . '.' . $counter . '.'. $key, $message);
                    }
                }

                $className = get_class($validator);
                $validator = $this->container->make($className); // New validator to make sure that messages are only inserted once
            }
            $counter++;
        }

        return true;
    }
}
