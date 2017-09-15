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
use Vallarj\JsonApi\Exception\InvalidFormatException;

class SingleResourceDocument extends AbstractDocument
{
    /** @var object The object bound to the document */
    private $boundObject;

    /**
     * Binds an object to the document
     * @param $object
     * @throws InvalidArgumentException
     */
    public function bind($object): void
    {
        if(is_object($object)) {
            $this->boundObject = $object;
        } else {
            throw InvalidArgumentException::fromSingleResourceDocumentBind();
        }
    }

    /**
     * @inheritdoc
     */
    public function getData(): array
    {
        // Return empty array if no bound object
        if(!$this->boundObject) {
            return [];
        }

        // Return empty array if no resource schema found
        if(!$this->hasPrimarySchema(get_class($this->boundObject))) {
            return [];
        }

        // Extract the document components (i.e., data and included)
        list($data, $included) = $this->extractDocumentComponents($this->boundObject);

        $root = [
            "data" => $data
        ];

        if(!empty($included)) {
            // For each included item, disassemble the array
            foreach($included as $items) {
                // First level: relationship types
                foreach($items as $item) {
                    // Second level: relationship ids

                    $root['included'][] = $item;
                }
            }
        }

        return $root;
    }

    /**
     * Sets the bound object. If a compatible object is currently bound, the properties of the object is replaced.
     * Otherwise, a new object is created based on primary ResourceSchemas.
     * @param array $root
     * @return array Array of changed property keys
     * @throws InvalidFormatException
     */
    public function setData(array $root): array
    {
        // Check if required fields are set
        if(!isset($root['data'])) {
            throw new InvalidFormatException("Key 'data' is required");
        } else if(!isset($root['data']['type'])) {
            throw new InvalidFormatException("Primary resource object type is required");
        }

        $inType = $root['data']['type'];
        $inId = $root['data']['id'] ?? null;

        // If an object is bound
        if($this->boundObject) {
            // Check if object is compatible
            // Get the equivalent schema of the bound object
            $schema = $this->getPrimarySchemaByClass(get_class($this->boundObject));

            // If not compatible
            if($schema->getResourceType() !== $inType) {
                // Get a compatible schema
                $schema = $this->getPrimarySchemaByType($inType);

                // Fail silently if no compatible schema was found.
                if(!$schema) {
                    return [];
                }

                // Create a compatible object and bind
                $class = $schema->getClass();
                $this->bind(new $class);
            }
        } else {
            // No bound object
            // Get a compatible schema
            $schema = $this->getPrimarySchemaByType($inType);

            // Fail silently if no compatible schema was found.
            if(!$schema) {
                return [];
            }

            // Create a compatible object and bind
            $class = $schema->getClass();
            $this->bind(new $class);
        }

        // Set ID
        $schema->setResourceId($this->boundObject, $inId);

        // Modified properties
        $modifiedKeys = [];

        // Set attributes
        if(isset($root['data']['attributes'])) {
            $attributes = $root['data']['attributes'];

            foreach($attributes as $key => $value) {
                if($schema->setResourceAttribute($this->boundObject, $key, $value)) {
                    $modifiedKeys[] = $key;
                }
            }
        }

        // Set relationships
        if(isset($root['data']['relationships'])) {
            $relationships = $root['data']['relationships'];

            if(!is_array($relationships)) {
                throw new InvalidFormatException("Resource relationships must be an array");
            }

            foreach($relationships as $key => $relationship) {
                if(!array_key_exists('data', $relationship)) {
                    throw new InvalidFormatException("Key 'data' required for relationships");
                }

                $relationshipData = $relationship['data'];

                if(is_null($relationshipData)) {
                    // Null relationship data implies to-one relationship
                    if($schema->setResourceRelationship($this->boundObject, $key, null)) {
                        $modifiedKeys[] = $key;
                    }
                } else if(is_array($relationshipData)) {
                    if(empty($relationshipData)) {
                        // Empty array relationship data implies to-many relationship
                        if($schema->setResourceRelationship($this->boundObject, $key, [])) {
                            $modifiedKeys[] = $key;
                        }
                    } else if(isset($relationshipData['id']) && isset($relationshipData['type'])) {
                        // This is a to-one relationship
                        if($schema->setResourceRelationship($this->boundObject, $key, $relationshipData)) {
                            $modifiedKeys[] = $key;
                        }
                    } else {
                        // This is a to-many relationship
                        $resources = [];

                        foreach($relationshipData as $resource) {
                            if(!isset($resource['id']) && !isset($resource['type'])) {
                                throw new InvalidFormatException("Relationship data must contain id and type keys.");
                            }

                            $resources[] = $resource;
                        }

                        if($schema->setResourceRelationship($this->boundObject, $key, $resources)) {
                            $modifiedKeys[] = $key;
                        }
                    }
                } else {
                    throw new InvalidFormatException("Relationship data must contain id and type keys.");
                }
            }
        }

        return $modifiedKeys;
    }
}