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


use Vallarj\JsonApi\Exception\InvalidSpecificationException;

class Attribute
{
    /** @var string Specifies the key of the attribute */
    private $key;

    /**
     * Attribute constructor.
     * @param string $key   The attribute key
     */
    function __construct(string $key)
    {
        $this->key = $key;
    }

    /**
     * Construct a Attribute from an array compatible
     * with schema attribute builder specifications
     * @param array $attributeSpecifications
     * @return Attribute
     * @throws InvalidSpecificationException
     */
    public static function fromArray(array $attributeSpecifications): Attribute
    {
        if(!isset($attributeSpecifications['key'])) {
            throw new InvalidSpecificationException("Index 'key' is required");
        }

        $instance = new self($attributeSpecifications['key']);
        return $instance;
    }

    /**
     * Returns the attribute key.
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Returns the value of the attribute
     * @param $parentObject
     * @return mixed    The value of the attribute
     */
    public function getValue($parentObject)
    {
        return $parentObject->{'get' . ucfirst($this->key)}();
    }

    /**
     * Sets the value of the attribute
     * @param $parentObject
     * @param $value
     */
    public function setValue($parentObject, $value): void
    {
        $parentObject->{'set' . ucfirst($this->key)}($value);
    }
}