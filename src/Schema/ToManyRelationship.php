<?php
/**
 *  Copyright 2017 Justin Dane D. Vallar
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


use Doctrine\Common\Inflector\Inflector;
use Vallarj\JsonApi\Exception\InvalidSpecificationException;
use Vallarj\JsonApi\Exception\InvalidValidatorException;

class ToManyRelationship implements ToManyRelationshipInterface
{
    /** @var string Specifies the key of the relationship */
    private $key = "";

    /** @var string Specifies the property name of the mapped relationship */
    private $mappedAs = "";

    /** @var bool Specifies if relationship is required. */
    private $isRequired = false;

    /** @var bool Validate relationship if null. Default is false. */
    private $validateIfEmpty = false;

    /** @var string[] Array of expected AbstractResourceSchema FQCNs */
    private $expectedSchemas = [];

    /** @var bool Specifies if relationship is readable */
    private $isReadable = true;

    /** @var bool Specifies if relationship is writable */
    private $isWritable = true;

    /** @var callable   Relationship validator  */
    private $validator = null;

    /**
     * @inheritdoc
     */
    public function setOptions(array $options): void
    {
        if(isset($options['key'])) {
            $this->key = $this->mappedAs = $options['key'];
        }

        if(isset($options['mappedAs'])) {
            $this->mappedAs = $options['mappedAs'];
        }

        if(isset($options['isReadable'])) {
            $this->setReadable($options['isReadable']);
        }

        if(isset($options['isWritable'])) {
            $this->setWritable($options['isWritable']);
        }

        if(isset($options['required'])) {
            $this->setRequired($options['required']);
        }

        if(isset($options['validate_if_empty'])) {
            $this->setValidateIfEmpty($options['validate_if_empty']);
        }

        if(isset($options['expects'])) {
            $expects = $options['expects'];

            if(!is_array($expects)) {
                throw new InvalidSpecificationException("Index 'expects' must be a compatible array");
            }

            $this->expectedSchemas = $expects;
        }

        if(isset($options['validator'])) {
            $validator = $options['validator'];

            if(!is_callable($validator)) {
                throw new InvalidSpecificationException("Index 'validator' must be a compatible callable.");
            }

            $this->setValidator($validator);
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
    public function getExpectedSchemas(): array
    {
        return $this->expectedSchemas;
    }

    /**
     * @inheritdoc
     */
    public function getCollection($parentObject): array
    {
        return $parentObject->{'get' . ucfirst($this->mappedAs)}();
    }

    /**
     * @inheritdoc
     */
    public function addItem($parentObject, $object): void
    {
        $singularMapping = Inflector::singularize($this->mappedAs);
        $parentObject->{'add' . ucfirst($singularMapping)}($object);
    }

    /**
     * @inheritdoc
     */
    public function clearCollection($parentObject): void
    {
        $collection = $this->getCollection($parentObject);

        // Assumes there is a remove function
        foreach($collection as $item) {
            $parentObject->{'remove' . ucfirst($this->mappedAs)}($item);
        }
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
     * Sets the isReadable flag of this relationship
     * @param bool $isReadable
     */
    private function setReadable(bool $isReadable)
    {
        $this->isReadable = $isReadable;
    }

    /**
     * Sets the isWritable flag of this relationship
     * @param bool $isWritable
     */
    private function setWritable(bool $isWritable)
    {
        $this->isWritable = $isWritable;
    }

    /**
     * @inheritdoc
     */
    public function isValid(array $relationships, $context): ValidationResultInterface
    {
        if(is_callable($this->validator)) {
            $validator = $this->validator;
            $result = $validator($relationships, $context);

            if(!$result instanceof ValidationResultInterface) {
                throw new InvalidValidatorException("Relationship validator must return an instance of ValidationResultInterface");
            }

            return $result;
        }

        return new ValidationResult(true);
    }

    /**
     * Callable that should return ValidationResultInterface
     * @param callable $validator
     */
    public function setValidator(callable $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @inheritdoc
     */
    public function validateIfEmpty(): bool
    {
        return $this->validateIfEmpty;
    }

    /**
     * Sets the validation if empty flag
     * @param bool $validateIfEmpty
     */
    public function setValidateIfEmpty(bool $validateIfEmpty)
    {
        $this->validateIfEmpty = $validateIfEmpty;
    }

    /**
     * @inheritdoc
     */
    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    /**
     * Sets the relationship required flag
     * @param bool $required
     */
    public function setRequired(bool $required)
    {
        $this->isRequired = $required;
    }
}