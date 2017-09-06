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
    /** @var string Specifies the resource type */
    private $type;

    /** @var string Specifies the FQCN of the object to bind this schema */
    private $class;

    /** @var SchemaAttribute[] Attributes of this schema */
    private $attributes;

    /** @var SchemaRelationship[] Relationships of this schema */
    private $relationships;

    /** @var string The property name of the object's identifier*/
    private $identifierPropertyName;

    /**
     * ResourceSchema constructor.
     * @param string $type      The resource type.
     * @param string $class     The FQCN of the object to bind this schema to
     */
    function __construct(string $type, string $class)
    {
        $this->type = $type;
        $this->class = $class;
        $this->attributes = [];
        $this->relationships = [];

        // Set defaults
        $this->identifierPropertyName = 'id';
    }

    /**
     * Construct a ResourceSchema from an array compatible
     * with ResourceSchema builder specifications
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

        // Resource bind class is required
        if(!isset($resourceSpecifications['class'])) {
            throw new InvalidSpecificationException("Index 'class' is required.");
        }

        // Create a new instance of ResourceSchema
        $instance = new self($resourceSpecifications['type'], $resourceSpecifications['class']);

        // Create attributes
        if(isset($resourceSpecifications['attributes']) && is_array($resourceSpecifications['attributes'])) {
            $attributeSpecs = $resourceSpecifications['attributes'];

            // Create an attribute for each spec given
            foreach($attributeSpecs as $item) {
                $instance->addAttribute($item);
            }
        }

        // Create relationships
        if(isset($resourceSpecifications['relationships']) && is_array($resourceSpecifications['relationships'])) {
            $relationshipSpecs = $resourceSpecifications['relationships'];

            // Create a relationship for each spec given
            foreach($relationshipSpecs as $item) {
                $instance->addRelationship($item);
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
     * Gets the FQCN of the object bindable to this schema
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Gets the attributes of this schema
     * @return SchemaAttribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Add a SchemaAttribute to the ResourceSchema.
     * If an attribute in the array with the same key exists, it will be replaced.
     * @param SchemaAttribute|array $attribute  If argument is an array, it must be compatible
     *                                          with the SchemaAttribute builder specifications.
     * @throws InvalidArgumentException
     */
    public function addAttribute($attribute): void
    {
        if($attribute instanceof SchemaAttribute) {
            $this->attributes[$attribute->getKey()] = $attribute;
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
     * Gets the identifier property name of the object to bind
     * @return string
     */
    public function getIdentifierPropertyName(): string
    {
        return $this->identifierPropertyName;
    }

    /**
     * Gets the relationships of this schema
     * @return SchemaRelationship[]
     */
    public function getRelationships(): array
    {
        return $this->relationships;
    }

    /**
     * Adds a SchemaRelationship to the ResourceSchema.
     * If a relationship in the array with the same key exists, it will be replaced.
     * @param SchemaRelationship|array $relationship    If argument is an array, it must be compatible with
     *                                                  the SchemaRelationship builder specifications
     * @throws InvalidArgumentException
     */
    public function addRelationship($relationship): void
    {
        if($relationship instanceof SchemaRelationship) {
            $this->relationships[$relationship->getKey()] = $relationship;
        } else if(is_array($relationship)) {
            $relationship = SchemaRelationship::fromArray($relationship);

            // Add to the relationships array with the key as index.
            $this->relationships[$relationship->getKey()] = $relationship;
        } else {
            // Must be a SchemaRelationship instance or a compatible array
            throw InvalidArgumentException::fromResourceSchemaAddRelationship();
        }
    }
}