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

class SchemaAttribute
{
    private $key;

    /**
     * SchemaAttribute constructor.
     * @param string $key   The attribute key
     */
    function __construct(string $key)
    {
        $this->key = $key;
    }

    /**
     * Construct a SchemaAttribute from an array compatible
     * with schema attribute builder specifications
     * @param array $attributeSpecifications
     * @return SchemaAttribute
     * @throws InvalidSpecificationException
     */
    public static function fromArray(array $attributeSpecifications): SchemaAttribute
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
}