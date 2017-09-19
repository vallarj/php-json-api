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

namespace Vallarj\JsonApi\Service;


use Vallarj\JsonApi\Exception\InvalidArgumentException;
use Vallarj\JsonApi\Schema\AbstractSchemaRelationship;
use Vallarj\JsonApi\Schema\ResourceSchema;
use Vallarj\JsonApi\Service\Options\EncoderServiceOptions;
use Vallarj\JsonApi\Service\Options\SchemaOptions;

class EncoderService
{
    private $schemaOptions;
    private $encoderOptions;

    private $schemaCache;

    /** @var array Holds the data for the current operation */
    private $data;

    /** @var array Holds the included resources of the current operation */
    private $included;

    /** @var bool Indicates if the last operation was successful */
    private $success;

    function __construct(SchemaOptions $schemaOptions, EncoderServiceOptions $encoderOptions)
    {
        $this->schemaOptions = $schemaOptions;
        $this->encoderOptions = $encoderOptions;

        $this->schemaCache = [];

        $this->initializeService();
    }

    public function encode($resource, array $schemaClasses): string
    {
        $this->initializeService();

        if (is_object($resource)) {
            $this->encodeSingleResource($resource, $schemaClasses);
        } else if (is_array($resource)) {
            $this->encodeResourceCollection($resource, $schemaClasses);
        } else {
            throw new InvalidArgumentException('Resource must be an object or an array of objects.');
        }

        // Disassemble included
        $included = [];
        foreach($this->included as $byType) {
            foreach($byType as $byId) {
                $included[] = $byId;
            }
        }

        // Encode the data
        return json_encode([
            "data" => $this->data,
            "included" => $included
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function initializeService()
    {
        $this->data = [];
        $this->included = [];
        $this->success = false;
    }

    private function encodeSingleResource($resource, array $schemaClasses): void
    {
        $resourceClass = get_class($resource);
        $compatibleSchema = null;

        foreach ($schemaClasses as $schemaClass) {
            if ($this->schemaOptions->hasClassCompatibleSchema($schemaClass, $resourceClass)) {
                $compatibleSchema = $schemaClass;
                break;
            }
        }

        if (!$compatibleSchema) {
            throw new InvalidArgumentException("No compatible schema found for the given resource object.");
        }

        // Extract resource data
        $this->data = $this->extractResource($resource, $compatibleSchema);
    }

    private function encodeResourceCollection(array $resources, array $schemaClasses): void
    {
        foreach($resources as $resource) {
            $resourceClass = get_class($resource);
            $compatibleSchema = null;

            foreach($schemaClasses as $schemaClass) {
                if($this->schemaOptions->hasClassCompatibleSchema($schemaClass, $resourceClass)) {
                    $compatibleSchema = $schemaClass;
                    break;
                }
            }

            if(!$compatibleSchema) {
                throw new InvalidArgumentException("No compatible schema found for a given resource object.");
            }

            // Extract resource data
            $this->data[] = $this->extractResource($resource, $compatibleSchema);
        }
    }

    private function getResourceSchema(string $schemaClass): ResourceSchema
    {
        if(!isset($this->schemaCache[$schemaClass])) {
            $this->schemaCache[$schemaClass] = new $schemaClass;
        }

        return $this->schemaCache[$schemaClass];
    }

    private function extractResource($object, string $schemaClass): array
    {
        // Get schema
        $schema = $this->getResourceSchema($schemaClass);

        // Extract attributes
        $attributes = [];
        $schemaAttributes = $schema->getAttributes();
        foreach($schemaAttributes as $schemaAttribute) {
            $key = $schemaAttribute->getKey();
            $attributes[$key] = $schemaAttribute->getValue($object);
        }

        // Extract relationships
        $relationships = [];
        $schemaRelationships = $schema->getRelationships();
        foreach($schemaRelationships as $schemaRelationship) {
            // Get the mapped object
            $mappedObject = $schemaRelationship->getMappedObject($object);

            // Get the expected schemas for this relationship
            $expectedSchemas = $schemaRelationship->getExpectedSchemas();

            if($schemaRelationship->getCardinality() === AbstractSchemaRelationship::TO_ONE) {
                // $mappedObject is a single object
                $relationship = $this->extractRelationship($mappedObject, $expectedSchemas);
                if($relationship) {
                    $relationships[$schemaRelationship->getKey()] = $relationship;
                }
            } else if($schemaRelationship->getCardinality() === AbstractSchemaRelationship::TO_MANY) {
                // $mappedObject is an array of objects
                foreach($mappedObject as $item) {
                    $relationship = $this->extractRelationship($item, $expectedSchemas);
                    if($relationship) {
                        $relationships[$schemaRelationship->getKey()][] = $relationship;
                    }

                }
            }
        }


        // Build the return data
        $data = [
            'type' => $this->schemaOptions->getResourceTypeBySchema($schemaClass),
            'id' => $schema->getResourceId($object),
            'attributes' => $attributes
        ];

        // Included relationships if not empty
        if(!empty($relationships)) {
            $data['relationships'] = $relationships;
        }

        return $data;
    }

    private function extractRelationship($mappedObject, array $expectedSchemas): ?array
    {
        foreach($expectedSchemas as $expectedSchema) {
            if($this->schemaOptions->hasClassCompatibleSchema($expectedSchema, get_class($mappedObject))) {
                // Schema instance
                $schema = $this->getResourceSchema($expectedSchema);

                // Get the resource type
                $objectType = $this->schemaOptions->getResourceTypeBySchema($expectedSchema);

                // Get the ID
                $objectID = $schema->getResourceId($mappedObject);

                // TODO: Condition for checking if included
                // Add included resource only once
                if(!isset($this->included[$objectType][$objectID])) {
                    // Indexing by type and ID ensures a unique resource is included only once
                    $this->included[$objectType][$objectID] = $this->extractResource($mappedObject, $expectedSchema);
                }

                return [
                    "data" => [
                        "type" => $objectType,
                        "id" => $objectID
                    ]
                ];
            }
        }

        return null;
    }
}