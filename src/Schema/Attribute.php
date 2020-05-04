<?php
/**
 *  Copyright 2017-2018 Justin Dane D. Vallar
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 *
 */

namespace Vallarj\JsonApi\Schema;


use Vallarj\JsonApi\Exception\InvalidSpecificationException;
use Laminas\Validator\ValidatorChain;
use Laminas\Validator\ValidatorInterface;

class Attribute implements AttributeInterface
{
    /** @var string Specifies the key of the attribute */
    private $key = "";

    /** @var bool Specifies if attribute is required. */
    private $isRequired = false;

    /** @var bool Specifies if attribute is readable */
    private $isReadable = true;

    /** @var bool Specifies is attribute is writable */
    private $isWritable = true;

    /** @var bool Validate attribute if null. Default is false. */
    private $validateIfEmpty = false;

    private $validatorChain;

    /**
     * Attribute constructor.
     */
    function __construct()
    {
        $this->validatorChain = new ValidatorChain();
    }

    /**
     * @inheritdoc
     */
    public function setOptions(array $options): void
    {
        if(isset($options['key'])) {
            $this->key = $options['key'];
        }

        if(isset($options['required'])) {
            $this->setRequired($options['required']);
        }

        if(isset($options['isReadable'])) {
            $this->setReadable($options['isReadable']);
        }

        if(isset($options['isWritable'])) {
            $this->setWritable($options['isWritable']);
        }

        if(isset($options['validate_if_empty'])) {
            $this->setValidateIfEmpty($options['validate_if_empty']);
        }

        if(isset($options['validators'])) {
            if(!is_array($options['validators'])) {
                throw new InvalidSpecificationException("Index 'validators' must be a compatible array");
            }

            $this->setValidators($options['validators']);
        }
    }

    /**
     * @inheritdoc
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @inheritdoc
     */
    public function getValue($parentObject)
    {
        return $parentObject->{'get' . ucfirst($this->key)}();
    }

    /**
     * @inheritdoc
     */
    public function setValue($parentObject, $value): void
    {
        $parentObject->{'set' . ucfirst($this->key)}($value);
    }

    /**
     * @inheritdoc
     */
    public function isReadable(): bool
    {
        return $this->isReadable;
    }

    /**
     * @inheritdoc
     */
    public function isWritable(): bool
    {
        return $this->isWritable;
    }

    /**
     * Sets the readable flag of this attribute
     * @param bool $isReadable
     */
    private function setReadable(bool $isReadable)
    {
        $this->isReadable = $isReadable;
    }

    /**
     * Sets the writable flag of this attribute
     * @param bool $isWritable
     */
    private function setWritable(bool $isWritable)
    {
        $this->isWritable = $isWritable;
    }

    /**
     * @inheritdoc
     */
    public function filterValue($value)
    {
        // If value is string
        if(is_string($value)) {
            // Trim whitespaces
            $value = trim($value);

            // If empty string, set value to null.
            if($value === "") {
                $value = null;
            }
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function isValid($value, $context): ValidationResultInterface
    {
        $validatorChain = $this->getValidatorChain();
        $validationResult = new ValidationResult($validatorChain->isValid($value, $context));
        $messages = $validatorChain->getMessages();

        foreach($messages as $message) {
            $validationResult->addMessage($message);
        }

        return $validationResult;
    }

    /**
     * @inheritdoc
     */
    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    /**
     * Sets the attribute required flag
     * @param bool $required
     */
    public function setRequired(bool $required)
    {
        $this->isRequired = $required;
    }

    /**
     * @inheritdoc
     */
    public function validateIfEmpty(): bool
    {
        return $this->validateIfEmpty;
    }

    /**
     * Sets the validation if null flag
     * @param bool $validateIfNull
     */
    public function setValidateIfEmpty(bool $validateIfNull)
    {
        $this->validateIfEmpty = $validateIfNull;
    }

    /**
     * Add a Validator
     * @param $validator
     * @throws InvalidSpecificationException
     */
    public function addValidator($validator): void
    {
        if(!$validator instanceof ValidatorInterface) {
            if(is_array($validator)) {
                if(!isset($validator['name'])) {
                    throw new InvalidSpecificationException("Index 'name' is required.");
                }

                $validatorClass = $validator['name'];
                $validatorOptions = $validator['options'] ?? [];

                $validator = new $validatorClass($validatorOptions);
            } else {
                throw new InvalidSpecificationException("Validator must be an instance of ValidatorInterface or " .
                    "a compatible array.");
            }
        }

        $this->getValidatorChain()->attach($validator);
    }

    /**
     * Gets the ValidatorChain
     * @return ValidatorChain
     */
    private function getValidatorChain(): ValidatorChain
    {
        return $this->validatorChain;
    }

    /**
     * Attach the Validators to the default ValidatorChain
     * @param array $validators
     * @throws InvalidSpecificationException
     */
    private function setValidators(array $validators): void
    {
        foreach($validators as $validator) {
            $this->addValidator($validator);
        }
    }
}