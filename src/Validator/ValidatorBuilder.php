<?php
/**
 * Bluz Framework Component
 *
 * @copyright Bluz PHP Team
 * @link https://github.com/bluzphp/framework
 */

/**
 * @namespace
 */
namespace Bluz\Validator;

use Bluz\Validator\Exception\ValidatorException;

/**
 * Validator Builder
 *
 * @package  Bluz\Validator
 * @author   Anton Shevchuk
 */
class ValidatorBuilder
{
    /**
     * Stack of validators
     *
     *   ['foo'] => [Validator, ...]
     *   ['bar'] => [Validator, ...]
     *
     * @var array
     */
    protected $validators = [];

    /**
     * @var array list of validation errors
     */
    protected $errors = [];

    /**
     * Add validator to builder
     * @param string    $name
     * @param Validator ...$validators
     * @return ValidatorBuilder
     */
    public function add($name, ...$validators)
    {
        if (isset($this->validators[$name])) {
            $this->validators[$name] = array_merge($this->validators[$name], $validators);
        } else {
            $this->validators[$name] = $validators;
        }
        return $this;
    }

    /**
     * Validate chain of rules
     *
     * @param  array|object $input
     * @return bool
     */
    public function validate($input)
    {
        $this->errors = [];
        $result = true;
        // check be validators
        foreach ($this->validators as $key => $validators) {
            if (!$this->validateItem($key, $input)) {
                $result = false;
            }
        }
        return $result;
    }

    /**
     * Validate chain of rules for single item
     *
     * @param  string $key
     * @param  array|object $input
     * @return bool
     */
    public function validateItem($key, $input)
    {
        // w/out any rules element is valid
        if (!isset($this->validators[$key])) {
            return true;
        }

        $validators = $this->validators[$key];

        // check be validators
        // extract input from ...
        if (is_array($input) && isset($input[$key])) {
            // array
            $value = $input[$key];
        } elseif (is_object($input) && isset($input->{$key})) {
            // object
            $value = $input->{$key};
        } else {
            // ... oh, not exists key
            // check chains for required
            $required = false;
            foreach ($validators as $validator) {
                /* @var Validator $validator */
                if ($validator->isRequired()) {
                    $required = true;
                    break;
                }
            }

            if ($required) {
                $value = '';
            } else {
                return true;
            }
        }

        // run validators chain
        foreach ($validators as $validator) {
            /* @var Validator $validator */
            if (!$validator->getName()) {
                // setup field name as property name
                $validator->setName(ucfirst($key));
            }

            if (!$validator->validate($value)) {
                if (!isset($this->errors[$key])) {
                    $this->errors[$key] = [];
                }
                $this->errors[$key][] = $validator->getError();
                return false;
            }
        }
        return true;
    }

    /**
     * Assert
     *
     * @param  mixed $input
     * @return bool
     * @throws ValidatorException
     */
    public function assert($input)
    {
        if (!$this->validate($input)) {
            $exception = new ValidatorException();
            $exception->setErrors($this->getErrors());
            throw $exception;
        }
        return true;
    }

    /**
     * Get errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
