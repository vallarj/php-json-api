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

    /** @var ResponseSchema[] Array of ResourceSchemas used by this relationship indexed by class */
    private $schemasByClass;

    /** @var ResponseSchema[] Array of ResourceSchemas used by this relationship indexed by type */
    private $schemasByType;

    /** @var bool Specifies if relationship is to be included in the document */
    private $included;

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
            throw InvalidArgumentException::fromSchemaRelationshipConstructor();
        }

        $this->key = $key;
        $this->cardinality = $cardinality;
        $this->included = $included;
        $this->schemasByClass = [];
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

        // Create schemas
        if(isset($relationshipSpecifications['schemas']) && is_array($relationshipSpecifications['schemas'])) {
            $resourceSchemas = $relationshipSpecifications['schemas'];

            // Create a ResponseSchema for each spec given
            foreach($resourceSchemas as $resourceSchema) {
                $instance->addSchema($resourceSchema);
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
     * Returns the ResponseSchema bindable with the given FQCN
     * @param string $class
     * @return null|ResponseSchema
     */
    public function getSchemaByClassName(string $class): ?ResponseSchema
    {
        if(isset($this->schemasByClass[$class])) {
            return $this->schemasByClass[$class];
        }

        return null;
    }

    /**
     * Adds a schema to this relationship
     * If a schema in the array with the same binding FQCN or type exists, it will be replaced.
     * @param ResponseSchema|array $schema  If argument is an array, it must be compatible with
     *                                      the ResponseSchema builder specifications
     * @throws InvalidArgumentException
     */
    public function addSchema($schema): void
    {
        if(!$schema instanceof ResponseSchema) {
            if(is_array($schema)) {
                // Create a ResponseSchema from compatible specifications array
                $schema = ResponseSchema::fromArray($schema);
            } else {
                // Must be a ResponseSchema instance or a compatible array
                throw InvalidArgumentException::fromSchemaRelationshipAddSchema();
            }
        }

        // Add schema to the schemas array with the bind class as index
        $this->schemasByClass[$schema->getClass()] = $schema;

        // If schema with type already exists
        if(isset($this->schemasByType[$schema->getType()])) {
            $oldSchema = $this->schemasByType[$schema->getType()];

            // If old schema class is not equal to the new schema class
            if($oldSchema->getClass() != $schema->getClass()) {
                // Remove oldSchema from schemas indexed by class
                unset($this->schemasByClass[$oldSchema->getClass()]);
            }
        }
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
    public function setIncluded(bool $included)
    {
        $this->included = $included;
    }
}