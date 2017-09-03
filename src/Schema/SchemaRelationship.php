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

class SchemaRelationship
{
    private $type;

    /**
     * SchemaRelationship constructor.
     * @param string $type  The relationship type
     */
    function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * Construct a SchemaRelationship from an array compatible
     * with schema relationship builder specifications
     * @param array $relationshipSpecifications
     * @return SchemaRelationship
     * @throws InvalidSpecificationException
     */
    public static function fromArray(array $relationshipSpecifications)
    {
        if(!isset($relationshipSpecifications['type'])) {
            throw new InvalidSpecificationException("Index 'type' is required.");
        }

        $instance = new self($relationshipSpecifications['type']);
        return $instance;
    }

    /**
     * Returns the relationship type.
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }
}