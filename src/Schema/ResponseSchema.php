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

class ResponseSchema
{
    /** @var string Specifies the resource type */
    private $type;

    /** @var string Specifies the FQCN of the object to bind this schema */
    private $class;

    /** @var ResponseSchemaAttribute[] Attributes of this schema */
    private $attributes;

    /** @var ResponseSchemaRelationship[] Relationships of this schema */
    private $relationships;

    /** @var string The property name of the object's identifier*/
    private $identifierPropertyName;

    /**
     * ResponseSchema constructor.
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
     * Construct a ResponseSchema from an array compatible
     * with ResponseSchema builder specifications
     * @param array $resourceSpecifications
     * @return ResponseSchema
     * @throws InvalidSpecificationException
     */
    public static function fromArray(array $resourceSpecifications): ResponseSchema
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
     * @return ResponseSchemaAttribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Add a ResponseSchemaAttribute to the ResponseSchema.
     * If an attribute in the array with the same key exists, it will be replaced.
     * @param ResponseSchemaAttribute|array $attribute  If argument is an array, it must be compatible
     *                                          with the ResponseSchemaAttribute builder specifications.
     * @throws InvalidArgumentException
     */
    public function addAttribute($attribute): void
    {
        if($attribute instanceof ResponseSchemaAttribute) {
            $this->attributes[$attribute->getKey()] = $attribute;
        } else if(is_array($attribute)) {
            $attribute = ResponseSchemaAttribute::fromArray($attribute);

            // Add to the attributes array with the key as index.
            $this->attributes[$attribute->getKey()] = $attribute;
        } else {
            // Must be a ResponseSchemaAttribute instance or a compatible array
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
     * @return ResponseSchemaRelationship[]
     */
    public function getRelationships(): array
    {
        return $this->relationships;
    }

    /**
     * Adds a ResponseSchemaRelationship to the ResponseSchema.
     * If a relationship in the array with the same key exists, it will be replaced.
     * @param ResponseSchemaRelationship|array $relationship    If argument is an array, it must be compatible with
     *                                                  the ResponseSchemaRelationship builder specifications
     * @throws InvalidArgumentException
     */
    public function addRelationship($relationship): void
    {
        if($relationship instanceof ResponseSchemaRelationship) {
            $this->relationships[$relationship->getKey()] = $relationship;
        } else if(is_array($relationship)) {
            $relationship = ResponseSchemaRelationship::fromArray($relationship);

            // Add to the relationships array with the key as index.
            $this->relationships[$relationship->getKey()] = $relationship;
        } else {
            // Must be a ResponseSchemaRelationship instance or a compatible array
            throw InvalidArgumentException::fromResourceSchemaAddRelationship();
        }
    }
}