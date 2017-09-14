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


use Vallarj\JsonApi\Exception\InvalidArgumentException;

abstract class AbstractSchemaRelationship
{
    const TO_ONE    =   "toOne";
    const TO_MANY   =   "toMany";

    /** @var string The cardinality of the relationship. */
    private $cardinality;

    /**
     * AbstractSchemaRelationship constructor.
     */
    function __construct()
    {
        $this->cardinality = self::TO_ONE;
    }

    /**
     * Returns the cardinality of the relationship
     * @return string
     */
    public function getCardinality(): string
    {
        return $this->cardinality;
    }

    /**
     * Sets the cardinality of the relationship. Must be one of
     * AbstractSchemaRelationship::TO_ONE, AbstractSchemaRelationship::TO_MANY
     * @param string $cardinality
     */
    public function setCardinality(string $cardinality): void
    {
        if($cardinality != self::TO_ONE && $cardinality != self::TO_MANY) {
            throw InvalidArgumentException::fromAbstractSchemaRelationshipSetCardinality();
        }

        $this->cardinality = $cardinality;
    }

    /**
     * Returns true if the resource should be included in the document
     * @return bool
     */
    abstract public function isIncluded(): bool;

    /**
     * Set options of this specification.
     * @param array $options    Array that contains the options for this specification.
     * @return mixed
     */
    abstract public function setOptions(array $options);

    /**
     * Returns the relationship key
     * @return string
     */
    abstract public function getKey(): string;

    /**
     * Extracts the relationship from the parent object in JSON-decoded array form
     * @param mixed $parentObject
     * @return array
     */
    abstract public function getRelationship($parentObject): array;

    /**
     * Gets the actual relationship resource object
     * @param $parentObject
     * @return mixed
     */
    abstract public function getMappedObject($parentObject);
}