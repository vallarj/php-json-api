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
use Vallarj\JsonApi\Schema\NestedSchemaRelationship;

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
        return $this->primarySchemas[$class] ?? null;
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
     * Checks if the document has a registered schema for a given included resource object class
     * @param string $class
     * @return bool
     */
    public function hasIncludedSchema(string $class): bool
    {
        return isset($this->includedSchemas[$class]);
    }

    /**
     * Returns a ResponseSchema from the array of included resource ResponseSchemas for the given class
     * @param string $class
     * @return null|ResponseSchema
     */
    public function getIncludedSchema(string $class): ?ResponseSchema
    {
        return $this->includedSchemas[$class] ?? null;
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

    /**
     * Extract the "data" and "included" document components for a single resource.
     * @param $boundObject
     * @param array &$included
     * @return array
     */
    final protected function extractDocumentComponents($boundObject, array &$included = []): array
    {
        // Find a compatible ResponseSchema for the bound object.
        $resourceSchema = $this->getPrimarySchema(get_class($boundObject));

        $data = $this->extractResource($boundObject, $resourceSchema);

        // Extract included
        $included = $this->extractIncluded($boundObject, $resourceSchema, $included);

        return array($data, $included);
    }

    /**
     * Extract resource from a given object and equivalent ResponseSchema
     * @param $object
     * @param ResponseSchema $resourceSchema
     * @return array
     */
    private function extractResource($object, ResponseSchema $resourceSchema)
    {
        // Extract attributes
        $attributes = $resourceSchema->getAttributes($object);

        // Extract relationships
        $relationships = $resourceSchema->getRelationships($object);

        // Build the return data
        $data = [
            "type" => $resourceSchema->getResourceType(),
            "id" => $resourceSchema->getResourceId($object),
            "attributes" => $attributes,
        ];

        // Include relationships if not empty
        if (!empty($relationships)) {
            $data['relationships'] = $relationships;
        }

        return $data;
    }

    /**
     * Extract included resources recursively from a given object, equivalent ResponseSchema and an
     * existing two-dimensional included array indexed by resource type and resource ID
     * @param $object
     * @param ResponseSchema $resourceSchema
     * @param array $included
     * @return array
     */
    private function extractIncluded($object, ResponseSchema $resourceSchema, array &$included): array
    {
        // Get included objects
        $includedObjects = $resourceSchema->getIncludedObjects($object);

        foreach($includedObjects as $includedObject) {
            $objectClass = get_class($includedObject);

            if($this->hasIncludedSchema($objectClass)) {
                $includedSchema = $this->getIncludedSchema($objectClass);

                $includedType = $includedSchema->getResourceType();
                $includedId = $includedSchema->getResourceId($includedObject);

                // Include resource only once;
                if(!isset($included[$includedType][$includedId])) {
                    // Indexing by type and ID ensures a unique resource is included only once
                    $included[$includedType][$includedId] = $this->extractResource($includedObject, $includedSchema);
                }

                // Recursively extract included objects
                $included = $this->extractIncluded($includedObject, $includedSchema, $included);
            }
        }

        return $included;
    }
}