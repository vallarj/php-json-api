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

class ResponseSchema extends ResourceIdentifierSchema
{
    /** @var ResponseSchemaAttribute[] Attributes of this schema */
    private $attributes;

    /** @var AbstractSchemaRelationship[] Relationships of this schema */
    private $relationships;

    /**
     * ResponseSchema constructor.
     * @param string $resourceType      The resource type.
     * @param string $class     The FQCN of the object to bind this schema to
     */
    function __construct(string $resourceType, string $class)
    {
        parent::__construct($resourceType, $class);

        $this->attributes = [];
        $this->relationships = [];
    }

    /**
     * Construct a ResponseSchema from an array compatible
     * with ResponseSchema builder specifications
     * @param array $resourceSpecifications
     * @return ResponseSchema
     * @throws InvalidSpecificationException
     */
    public static function fromArray(array $resourceSpecifications)
    {
        // Resource type is required
        if(!isset($resourceSpecifications['type'])) {
            throw new InvalidSpecificationException("Index 'type' is required.");
        }

        // Resource bind class is required
        if(!isset($resourceSpecifications['class'])) {
            throw new InvalidSpecificationException("Index 'class' is required.");
        }

        // Create a new instance of ResponseSchema
        $instance = new static($resourceSpecifications['type'], $resourceSpecifications['class']);

        // Create attributes
        if(isset($resourceSpecifications['attributes']) && is_array($resourceSpecifications['attributes'])) {
            $attributeSpecs = $resourceSpecifications['attributes'];

            // Create an attribute for each spec given
            foreach($attributeSpecs as $item) {
                $instance->addSchemaAttribute($item);
            }
        }

        // Create relationships
        if(isset($resourceSpecifications['relationships']) && is_array($resourceSpecifications['relationships'])) {
            $relationshipSpecs = $resourceSpecifications['relationships'];

            // Create a relationship for each spec given
            foreach($relationshipSpecs as $item) {
                $instance->addSchemaRelationship($item);
            }
        }

        if(isset($resourceSpecifications['identifier'])) {
            $instance->setIdentifierPropertyName($resourceSpecifications['identifier']);
        }

        // Return the instance
        return $instance;
    }

    /**
     * Extract the resource attributes from the given object
     * @param mixed $object     The object to extract the attributes from
     * @return array            Key-value pair of the resource attributes
     */
    public function getAttributes($object): array
    {
        $attributes = [];

        foreach ($this->attributes as $schemaAttribute) {
            $key = $schemaAttribute->getKey();
            $attributes[$key] = $schemaAttribute->getAttribute($object);
        }

        return $attributes;
    }

    /**
     * Add a ResponseSchemaAttribute to the ResponseSchema.
     * If an attribute in the array with the same key exists, it will be replaced.
     * @param ResponseSchemaAttribute|array $attribute  If argument is an array, it must be compatible
     *                                                  with the ResponseSchemaAttribute builder specifications.
     * @throws InvalidArgumentException
     */
    public function addSchemaAttribute($attribute): void
    {
        if($attribute instanceof ResponseSchemaAttribute) {
            $this->attributes[$attribute->getKey()] = $attribute;
        } else if(is_array($attribute)) {
            $attribute = ResponseSchemaAttribute::fromArray($attribute);

            // Add to the attributes array with the key as index.
            $this->attributes[$attribute->getKey()] = $attribute;
        } else {
            // Must be a ResponseSchemaAttribute instance or a compatible array
            throw InvalidArgumentException::fromResponseSchemaAddSchemaAttribute();
        }
    }

    /**
     * Extract the resource relationships from the given object
     * @param mixed $object     The object to extract the relationships from
     * @return array            Array containing the relationships
     */
    public function getRelationships($object): array
    {
        $relationships = [];

        foreach($this->relationships as $schemaRelationship) {
            $key = $schemaRelationship->getKey();
            $relationships[$key] = $schemaRelationship->getRelationship($object);
        }

        return $relationships;
    }

    /**
     * Adds an AbstractSchemaRelationship to the ResponseSchema.
     * If a relationship in the array with the same key exists, it will be replaced.
     * @param AbstractSchemaRelationship|array $relationship    If argument is an array, it must be compatible with
     *                                                          the ResponseSchemaRelationship builder specifications
     * @throws InvalidArgumentException
     * @throws InvalidSpecificationException
     */
    public function addSchemaRelationship($relationship): void
    {
        if(!$relationship instanceof AbstractSchemaRelationship) {
            if(is_array($relationship)) {
                if(!isset($relationship["bindType"]) || !is_string($relationship["bindType"])) {
                    throw new InvalidSpecificationException("Index 'bindType' is required");
                }

                if(isset($relationship["options"]) && !is_array($relationship['options'])) {
                    throw new InvalidSpecificationException("Index 'options' must be a compatible array.");
                }

                $bindType = $relationship['bindType'];
                $options = $relationship['options'];

                if(!is_subclass_of($bindType, AbstractSchemaRelationship::class)) {
                    throw new InvalidSpecificationException("Index 'bindType' must be a class that extends " .
                        "AbstractSchemaRelationship.");
                }

                /** @var AbstractSchemaRelationship $relationship */
                $relationship = new $bindType;
                $relationship->setOptions($options);
            }

            else InvalidArgumentException::fromResponseSchemaAddRelationship();
        }

        // Add to the relationships array with the key as index.
        $this->relationships[$relationship->getKey()] = $relationship;
    }

    /**
     * Returns an array of resource objects for inclusion
     * @param object $parentObject  The root object to extract the included resources from
     * @return array
     */
    public function getIncludedObjects($parentObject): array
    {
        $included = [];

        foreach($this->relationships as $schemaRelationship) {
            // If current schemaRelationship is included
            if($schemaRelationship->isIncluded()) {
                if($schemaRelationship->getCardinality() === AbstractSchemaRelationship::TO_ONE) {
                    // Get the relationship resource object
                    $included[] = $schemaRelationship->getMappedObject($parentObject);

                } else if($schemaRelationship->getCardinality() === AbstractSchemaRelationship::TO_MANY) {
                    // Resources are assumed to be in an array
                    $included = array_merge($included, $schemaRelationship->getMappedObject($parentObject));
                }
            }
        }

        return $included;
    }
}