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
use Vallarj\JsonApi\Exception\InvalidSpecificationException;

class ResponseSchemaRelationship
{
    const TO_ONE    =   "toOne";
    const TO_MANY   =   "toMany";

    /** @var string Specifies the key of the relationship */
    private $key;

    /** @var string Specifies relationship cardinality */
    private $cardinality;

    /** @var bool Specifies if relationship is to be included in the document */
    private $included;

    /** @var ResourceIdentifierSchema[] Array of ResourceIdentifierSchema for each expected class */
    private $expectedResources;

    /**
     * ResponseSchemaRelationship constructor.
     * @param string $key           Specifies the relationship key
     * @param string $cardinality   Specifies relationship cardinality (to-One or to-Many)
     * @param bool $included        Specifies if this relationship is to be included in the document.
     *                              Defaults to false.
     * @throws InvalidArgumentException
     */
    function __construct(string $key, string $cardinality, bool $included = false)
    {
        if($cardinality != self::TO_ONE && $cardinality != self::TO_MANY) {
            throw InvalidArgumentException::fromResponseSchemaRelationshipConstructor();
        }

        $this->key = $key;
        $this->cardinality = $cardinality;
        $this->included = $included;
        $this->expectedResources = [];
    }

    /**
     * Construct a ResponseSchemaRelationship from an array compatible
     * with relationship builder specifications
     * @param array $relationshipSpecifications
     * @return ResponseSchemaRelationship
     * @throws InvalidSpecificationException
     */
    public static function fromArray(array $relationshipSpecifications): ResponseSchemaRelationship
    {
        if(!isset($relationshipSpecifications['key'])) {
            throw new InvalidSpecificationException("Index 'key' is required.");
        }

        if(!isset($relationshipSpecifications['cardinality'])) {
            throw new InvalidSpecificationException("Index 'cardinality' is required");
        }

        $included = isset($relationshipSpecifications['included']) ? $relationshipSpecifications['included'] : false;

        // Create a new instance of ResponseSchemaRelationship
        $instance = new self($relationshipSpecifications['key'], $relationshipSpecifications['cardinality'], $included);

        if(isset($relationshipSpecifications['expects'])) {
            $expects = $relationshipSpecifications['expects'];

            if(!is_array($expects)) {
                throw new InvalidSpecificationException("Index 'expects' must be an array.");
            }

            foreach($expects as $resourceIdentifier) {
                $instance->addExpectedResource($resourceIdentifier);
            }
        }

        // Return the instance
        return $instance;
    }

    /**
     * Gets the key of the relationship
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Gets the cardinality of the relationship
     * @return string
     */
    public function getCardinality(): string
    {
        return $this->cardinality;
    }

    /**
     * Checks if this relationship is to be included in the document
     * @return bool
     */
    public function isIncluded(): bool
    {
        return $this->included;
    }

    /**
     * Sets whether or not this relationship is going to be included in the document
     * @param bool $included
     */
    public function setIncluded(bool $included): void
    {
        $this->included = $included;
    }

    /**
     * Add a type mapping for the expected class name
     * Replaces a class mapping if it already exists
     * @param array|ResourceIdentifierSchema $resourceIdentifier
     * @throws InvalidArgumentException
     */
    public function addExpectedResource($resourceIdentifier): void
    {
        if(!$resourceIdentifier instanceof ResourceIdentifierSchema) {
            if(!is_array($resourceIdentifier)) {
                throw InvalidArgumentException::fromResponseSchemaRelationshipAddExpectedResource();
            }

            $resourceIdentifier = ResourceIdentifierSchema::fromArray($resourceIdentifier);
        }

        $this->expectedResources[$resourceIdentifier->getClass()] = $resourceIdentifier;
    }

    /**
     * Gets the mapped type by class name
     * Returns null if class name was not found
     * @param string $class
     * @return null|ResourceIdentifierSchema
     */
    public function getExpectedResourceByClassName(string $class): ?ResourceIdentifierSchema
    {
        if(isset($this->expectedResources[$class])) {
            return $this->expectedResources[$class];
        }

        return null;
    }
}