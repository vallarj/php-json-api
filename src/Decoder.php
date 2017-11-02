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

namespace Vallarj\JsonApi;


use Vallarj\JsonApi\Error\Error;
use Vallarj\JsonApi\Error\ErrorDocument;
use Vallarj\JsonApi\Error\Source\AttributePointer;
use Vallarj\JsonApi\Exception\InvalidFormatException;
use Vallarj\JsonApi\Schema\AbstractResourceSchema;
use Vallarj\JsonApi\Schema\AttributeInterface;
use Vallarj\JsonApi\Schema\ToManyRelationshipInterface;
use Vallarj\JsonApi\Schema\ToOneRelationshipInterface;

class Decoder
{
    /** @var AbstractResourceSchema[]   Cache of instantiated schemas */
    private $schemaCache;

    /** @var array  Cache of instantiated objects */
    private $objectCache;

    /** @var string[]   Modified property keys from the last decoding operation */
    private $modifiedProperties;

    /** @var Error[]  Errors of the last decoding operation */
    private $errors;

    /**
     * Decoder constructor.
     */
    function __construct()
    {
        $this->schemaCache = [];
        $this->objectCache = [];

        $this->initialize();
    }

    /**
     * Decodes the document into a new object from a compatible schema.
     * @param string $data
     * @param array $schemaClasses
     * @param bool $ignoreMissingFields
     * @return mixed
     * @throws InvalidFormatException
     */
    public function decode(string $data, array $schemaClasses, bool $ignoreMissingFields = false)
    {
        $this->initialize();

        // Decode root object
        $root = json_decode($data, true);

        if(json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidFormatException("Invalid document format.");
        }

        // Check if data key is set
        if(!array_key_exists('data', $root)) {
            throw new InvalidFormatException("Key 'data' is required");
        }

        $data = $root['data'];

        if(is_null($data)) {
            // Empty single resource
            return null;
        }

        if(!is_array($data)) {
            throw new InvalidFormatException("Invalid 'data' format");
        }

        if(array() === $data) {
            // Empty resource collection
            return [];
        }

        // Check if data is a single resource or a resource collection
        if(array_keys($data) !== range(0, count($data) - 1)) {
            // Array is sequentially indexed, possibly a resource collection
            $resource = $this->decodeSingleResource($data, $schemaClasses, $ignoreMissingFields);
        } else {
            // Array is possibly a single resource
            $resource = $this->decodeResourceCollection($data, $schemaClasses, $ignoreMissingFields);
        }

        // Return null if errors occurred
        return $this->hasValidationErrors() ? null : $resource;
    }

    /**
     * Check if last operation has validation errors
     * @return bool
     */
    public function hasValidationErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Return the modified property keys from the last operation
     * @return array
     */
    public function getModifiedProperties(): array
    {
        return $this->modifiedProperties;
    }

    /**
     * Prepare the service for an encoding operation
     */
    private function initialize(): void
    {
        $this->modifiedProperties = [];
        $this->errors = [];
    }

    /**
     * Decode a single resource.
     * @param array $data
     * @param array $schemaClasses
     * @param bool $ignoreMissingFields
     * @return mixed
     * @throws InvalidFormatException
     */
    private function decodeSingleResource(array $data, array $schemaClasses, bool $ignoreMissingFields)
    {
        // Check if 'type' key is set
        if(!isset($data['type'])) {
            throw new InvalidFormatException("Resource 'type' is required");
        }

        $resourceType = $data['type'];
        $compatibleSchema = null;

        foreach($schemaClasses as $schemaClass) {
            $schema = $this->getResourceSchema($schemaClass);
            if($schema->getResourceType() == $resourceType) {
                $compatibleSchema = $schema;
                break;
            }
        }

        if(!$compatibleSchema) {
            throw new InvalidFormatException("Invalid 'type' given for this resource");
        }

        return $this->createResourceObject($data, $compatibleSchema, $ignoreMissingFields);
    }

    /**
     * Decode a resource collection.
     * @param array $data
     * @param array $schemaClasses
     * @param bool $ignoreMissingFields
     * @return mixed
     * @throws InvalidFormatException
     */
    private function decodeResourceCollection(array $data, array $schemaClasses, bool $ignoreMissingFields)
    {
        $collection = [];

        foreach($data as $item) {
            if(!isset($item['type'])) {
                throw new InvalidFormatException("Resource 'type' is required");
            }

            $resourceType = $item['type'];
            $compatibleSchema = null;

            foreach($schemaClasses as $schemaClass) {
                $schema = $this->getResourceSchema($schemaClass);
                if($schema->getResourceType() == $resourceType) {
                    $compatibleSchema = $schema;
                    break;
                }
            }

            if(!$compatibleSchema) {
                throw new InvalidFormatException("Invalid 'type' given for this resource");
            }

            $object = $this->createResourceObject($item, $compatibleSchema, $ignoreMissingFields);

            if($object) {
                $collection[] = $object;
            }
        }

        return $collection;
    }

    private function getResourceSchema(string $schemaClass): AbstractResourceSchema
    {
        if(!isset($this->schemaCache[$schemaClass])) {
            $this->schemaCache[$schemaClass] = new $schemaClass;
        }

        return $this->schemaCache[$schemaClass];
    }

    public function getErrorDocument(): ?ErrorDocument
    {
        if(!empty($this->errors)) {
            $errorDocument = new ErrorDocument("422");
            foreach($this->errors as $error) {
                $errorDocument->addError($error);
            }
            return $errorDocument;
        }

        return null;
    }

    public function addError(string $attribute, string $detail): void
    {
        $error = new Error();
        $error->setSource(new AttributePointer($attribute));
        $error->setDetail($detail);
        $this->errors[] = $error;
    }

    private function createResourceObject(array $data, AbstractResourceSchema $schema, bool $ignoreMissingFields)
    {
        $resourceType = $data['type'];
        $resourceId = $data['id'] ?? null;

        // Return cached object if already cached
        if (!is_null($resourceId) && isset($this->objectCache[$resourceType][$resourceId])) {
            return $this->objectCache[$resourceType][$resourceId];
        }

        // Get the resource class
        $resourceClass = $schema->getMappingClass();

        // Create the object
        $object = new $resourceClass;

        // Set the resource ID
        $schema->setResourceId($object, $resourceId);

        // Set attributes
        if (isset($data['attributes'])) {
            $attributes = $data['attributes'];
            $schemaAttributes = $schema->getAttributes();

            foreach($schemaAttributes as $schemaAttribute) {
                if($schemaAttribute->getAccessType() & AttributeInterface::ACCESS_WRITE) {
                    $key = $schemaAttribute->getKey();

                    if(array_key_exists($key, $attributes)) {
                        $value = $attributes[$key];
                        // Perform attribute pre-processing
                        $value = $schemaAttribute->filterValue($value);

                        if(is_null($value)) {
                            if($schemaAttribute->isRequired()) {
                                $this->addError($key, "Field is required.");
                            } else {
                                $schemaAttribute->setValue($object, $value);
                                $this->modifiedProperties[] = $key;
                            }
                        } else {
                            if($schemaAttribute->isValid($value)) {
                                $schemaAttribute->setValue($object, $value);
                                $this->modifiedProperties[] = $key;
                            } else {
                                $errorMessages = $schemaAttribute->getErrorMessages();
                                foreach($errorMessages as $errorMessage) {
                                    $this->addError($key, $errorMessage);
                                }
                            }
                        }
                    } else if(!$ignoreMissingFields && $schemaAttribute->isRequired()) {
                        $this->addError($key, "Field is required.");
                    }
                }
            }
        }

        // Set relationships
        if (isset($data['relationships'])) {
            $relationships = $data['relationships'];
            $schemaRelationships = $schema->getRelationships();

            if(!is_array($relationships)) {
                throw new InvalidFormatException("Invalid format for relationships.");
            }

            foreach($schemaRelationships as $schemaRelationship) {
                if($schemaRelationship instanceof ToOneRelationshipInterface &&
                    ($schemaRelationship->getAccessType() & ToOneRelationshipInterface::ACCESS_WRITE)) {
                    $expectedSchemas = $schemaRelationship->getExpectedSchemas();
                    $key = $schemaRelationship->getKey();

                    if(isset($relationships[$key])) {
                        if(!is_array($relationships[$key])) {
                            throw new InvalidFormatException("Invalid format for relationships.");
                        }

                        if($this->hydrateToOneRelationship($schemaRelationship, $object, $relationships[$key],
                            $expectedSchemas)) {
                            $this->modifiedProperties[] = $key;
                        }
                    }
                } else if($schemaRelationship instanceof ToManyRelationshipInterface &&
                    ($schemaRelationship->getAccessType() & ToManyRelationshipInterface::ACCESS_WRITE)) {
                    $expectedSchemas = $schemaRelationship->getExpectedSchemas();
                    $key = $schemaRelationship->getKey();

                    if(isset($relationships[$key])) {
                        if(!is_array($relationships[$key])) {
                            throw new InvalidFormatException("Invalid format for relationships.");
                        }

                        if($this->hydrateToManyRelationship($schemaRelationship, $object, $relationships[$key],
                            $expectedSchemas)) {
                            $this->modifiedProperties[] = $key;
                        }
                    }
                }
            }
        }

        return $object;
    }

    private function hydrateToOneRelationship(
        ToOneRelationshipInterface $schemaRelationship,
        $parentObject,
        array $relationship,
        array $expectedSchemas
    ): bool
    {
        if(!array_key_exists('data', $relationship)) {
            throw new InvalidFormatException("Key 'data' required for relationships");
        }

        $relationshipData = $relationship['data'];
        if(is_null($relationshipData)) {
            $schemaRelationship->clearObject($parentObject);
            return true;
        }

        if(!is_array($relationshipData) || !isset($relationshipData['type']) || !isset($relationshipData['id'])) {
            throw new InvalidFormatException("Invalid format for relationships.");
        }

        foreach($expectedSchemas as $schemaClass) {
            $schema = $this->getResourceSchema($schemaClass);
            if($schema->getResourceType() == $relationshipData['type']) {
                $object = $this->resolveRelationshipObject($schema, $relationshipData['id']);
                $schemaRelationship->setObject($parentObject, $object);
                return true;
            }
        }

        return false;
    }

    private function hydrateToManyRelationship(
        ToManyRelationshipInterface $schemaRelationship,
        $parentObject,
        array $relationship,
        array $expectedSchemas
    ): bool
    {
        if(!array_key_exists('data', $relationship)) {
            throw new InvalidFormatException("Key 'data' required for relationships");
        }

        $relationshipData = $relationship['data'];
        if(!is_array($relationshipData)) {
            throw new InvalidFormatException("Invalid format for to-many relationships");
        }

        if(empty($relationshipData)) {
            // Clear collection
            $schemaRelationship->clearCollection($parentObject);
        }

        $modifiedCount = 0;
        foreach($relationshipData as $item) {
            if(!is_array($item) || !isset($item['type']) || !isset($item['id'])) {
                throw new InvalidFormatException("Invalid format for to-many relationships");
            }

            foreach($expectedSchemas as $schemaClass) {
                $schema = $this->getResourceSchema($schemaClass);
                if($schema->getResourceType() == $item['type']) {
                    $object = $this->resolveRelationshipObject($schema, $item['id']);
                    $schemaRelationship->addItem($parentObject, $object);
                    $modifiedCount++;
                    break;
                }
            }
        }

        return $modifiedCount > 0;
    }

    private function resolveRelationshipObject(AbstractResourceSchema $schema, $id)
    {
        $mappingClass = $schema->getMappingClass();

        if(!isset($this->objectCache[$mappingClass][$id])) {
            $object = new $mappingClass;
            $schema->setResourceId($object, $id);
            $this->objectCache[$mappingClass][$id] = $object;
        }

        return $this->objectCache[$mappingClass][$id];
    }
}