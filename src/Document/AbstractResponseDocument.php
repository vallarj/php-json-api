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

namespace Vallarj\JsonApi\Document;


use Vallarj\JsonApi\Exception\InvalidArgumentException;
use Vallarj\JsonApi\Schema\ResponseSchema;
use Vallarj\JsonApi\Schema\ResponseSchemaAttribute;
use Vallarj\JsonApi\Schema\ResponseSchemaRelationship;

abstract class AbstractResponseDocument
{
    /** @var ResponseSchema[] Array of ResponseSchemas used by the document */
    private $primarySchemas;

    /** @var ResponseSchema[] Array of ResponseSchemas for included resources */
    private $includedSchemas;

    /**
     * AbstractResponseDocument constructor.
     */
    function __construct()
    {
        $this->primarySchemas = [];
    }

    /**
     * Checks if the document has a registered schema for a given class
     * @param string $class
     * @return bool
     */
    public function hasPrimarySchema(string $class): bool
    {
        return isset($this->primarySchemas[$class]);
    }

    /**
     * Returns a ResponseSchema from the array of primary resource ResponseSchemas for the given class
     * @param string $class
     * @return null|ResponseSchema
     */
    public function getPrimarySchema(string $class): ?ResponseSchema
    {
        if($this->hasPrimarySchema($class)) {
            return $this->primarySchemas[$class];
        }

        return null;
    }

    /**
     * Adds a ResponseSchema to the list of ResponseSchemas that the document can use to bind an object as a
     * primary resource
     * If a schema in the array with the same class exists, it will be replaced.
     * @param ResponseSchema|array $primarySchema  If argument is an array, it must be compatible with
     *                                              the ResponseSchema builder specifications
     * @throws InvalidArgumentException
     */
    public function addPrimarySchema($primarySchema): void
    {
        if($primarySchema instanceof ResponseSchema) {
            $this->primarySchemas[$primarySchema->getClass()] = $primarySchema;
        } else if(is_array($primarySchema)) {
            $primarySchema = ResponseSchema::fromArray($primarySchema);

            // Add to the schemas array with the class as index
            $this->primarySchemas[$primarySchema->getClass()] = $primarySchema;
        } else {
            // Must be a ResponseSchema instance or a compatible array
            throw InvalidArgumentException::fromAbstractResponseDocumentAddSchema();
        }
    }

    /**
     * Returns a ResponseSchema from the array of included resource ResponseSchemas for the given class
     * @param string $class
     * @return null|ResponseSchema
     */
    public function getIncludedSchema(string $class): ?ResponseSchema
    {
        if(isset($this->includedSchemas[$class])) {
            return $this->includedSchemas[$class];
        }

        return null;
    }

    /**
     * Adds a ResponseSchema to the list of ResponseSchemas that the document can use for resource inclusion
     * If a schema in the array with the same class exists, it will be replaced.
     * @param ResponseSchema|array $includedSchema  If argument is an array, it must be compatible with
     *                                              the ResponseSchema builder specifications
     * @throws InvalidArgumentException
     */
    public function addIncludedSchema($includedSchema): void
    {
        if($includedSchema instanceof ResponseSchema) {
            $this->includedSchemas[$includedSchema->getClass()] = $includedSchema;
        } else if(is_array($includedSchema)) {
            $includedSchema = ResponseSchema::fromArray($includedSchema);

            // Add to the schemas array with the class as index
            $this->includedSchemas[$includedSchema->getClass()] = $includedSchema;
        } else {
            // Must be a ResponseSchema instance or a compatible array
            throw InvalidArgumentException::fromAbstractResponseDocumentAddSchema();
        }
    }

    /**
     * Gets a JSON API equivalent array
     * @return array
     */
    abstract public function getData(): array;
}