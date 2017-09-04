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

class ResourceSchema
{
    private $type;
    private $attributes;
    private $relationships;

    /**
     * ResourceSchema constructor.
     * @param string $type  The resource type.
     */
    function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * Construct a ResourceSchema from an array compatible
     * with schema relationship builder specifications
     * @param array $resourceSpecifications
     * @return ResourceSchema
     * @throws InvalidSpecificationException
     */
    public static function fromArray(array $resourceSpecifications): ResourceSchema
    {
        // Resource type is required
        if(!isset($resourceSpecifications['type'])) {
            throw new InvalidSpecificationException("Index 'type' is required.");
        }

        // Create a new instance of ResourceSchema
        $instance = new self($resourceSpecifications['type']);

        // Create attributes
        if(isset($resourceSpecifications['attributes']) && is_array($resourceSpecifications['attributes'])) {
            $attributeSpecs = $resourceSpecifications['attributes'];

            // Create an attribute for each spec given
            foreach($attributeSpecs as $item) {
                if(!is_array($item)) {
                    throw new InvalidSpecificationException("Attribute specification must be a compatible array.");
                }

                // Create an attribute and push into attributes array
                $instance->attributes[] = SchemaAttribute::fromArray($item);
            }
        }

        // Create relationships
        if(isset($resourceSpecifications['relationships']) && is_array($resourceSpecifications['relationships'])) {
            $relationshipSpecs = $resourceSpecifications['relationships'];

            // Create a relationship for each spec given
            foreach($relationshipSpecs as $item) {
                if(!is_array($item)) {
                    throw new InvalidSpecificationException("Relationship specification must be a compatible array.");
                }

                // Create a relationship and push into relationships array
                $instance->relationships[] = ResourceSchema::fromArray($item);
            }
        }

        // Return the instance
        return $instance;
    }

    /**
     * Gets the type of the resource.
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Add a SchemaAttribute to the ResourceSchema.
     * If an attribute in the array with the same key exists, it will be replaced.
     * @param SchemaAttribute|array $attribute  If argument is an array, it must be compatible
     *                                          with the schema attribute builder specifications.
     * @throws InvalidArgumentException
     */
    public function addAttribute($attribute): void
    {
        if($attribute instanceof SchemaAttribute) {
            $this->attributes[] = $attribute;
        } else if(is_array($attribute)) {
            $attribute = SchemaAttribute::fromArray($attribute);

            // Add to the attributes array with the key as index.
            $this->attributes[$attribute->getKey()] = $attribute;
        } else {
            // Must be a SchemaAttribute instance or a compatible array
            throw InvalidArgumentException::fromResourceSchemaAddAttribute();
        }
    }

    /**
     * Adds a ResourceSchema relationship to the ResourceSchema.
     * If a relationship in the array with the same type exists, it will be replaced.
     * @param ResourceSchema|array $relationship    If argument is an array, it must be compatible with
     *                                              the ResourceSchema builder specifications
     * @throws InvalidArgumentException
     */
    public function addRelationship($relationship): void
    {
        if($relationship instanceof ResourceSchema) {
            $this->relationships[] = $relationship;
        } else if(is_array($relationship)) {
            $relationship = ResourceSchema::fromArray($relationship);

            // Add to the relationships array with the type as index.
            $this->relationships[$relationship->getType()] = $relationship;
        } else {
            // Must be a ResourceSchema instance or a compatible array
            throw InvalidArgumentException::fromResourceSchemaAddRelationship();
        }
    }
}