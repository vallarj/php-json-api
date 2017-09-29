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

abstract class AbstractResourceSchema
{
    /** @var string Specifies the resource type */
    protected $resourceType;

    /** @var string Specifies the FQCN of the object to map the JSON API resource */
    protected $mappingClass;

    /** @var string The property name of the object's identifier */
    protected $identifier = "id";

    /** @var SchemaAttribute[] Attributes of this schema */
    private $attributes;

    /** @var AbstractSchemaRelationship[] Relationships of this schema */
    private $relationships;

    /**
     * AbstractResourceSchema constructor.
     */
    public function __construct()
    {
        $this->attributes = [];
        $this->relationships = [];

        // Create attributes
        $attributeSpecs = $this->getAttributes();

        // Create an attribute for each spec given
        foreach($attributeSpecs as $item) {
            $this->addSchemaAttribute($item);
        }

        // Create relationships
        $relationshipSpecs = $this->getRelationships();

        // Create relationship for each spec given
        foreach($relationshipSpecs as $item) {
            $this->addSchemaRelationship($item);
        }
    }

    /**
     * Must return the resource type used by this schema
     * @return string
     */
    public function getResourceType(): ?string
    {
        return $this->resourceType;
    }

    /**
     * Must return the FQCN of the object to map the JSON API resource
     * @return string
     */
    public function getMappingClass(): ?string
    {
        return $this->mappingClass;
    }

    /**
     * Must return the identifier property name of the object to bind
     * @return string
     */
    public function getIdentifierPropertyName(): string
    {
        return $this->identifier;
    }

    /**
     * Must return an array of SchemaAttributes or a compatible SchemaAttribute specifications array
     * @return array
     */
    public function getAttributes(): array
    {
        return [];
    }

    /**
     * Must return an array of AbstractSchemaRelationship or a compatible
     * AbstractSchemaRelationship specifications array
     * @return array
     */
    public function getRelationships(): array
    {
        return [];
    }

    /**
     * Returns the SchemaAttributes of this schema
     * @return SchemaAttribute[]
     */
    final public function getSchemaAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Returns the AbstractSchemaRelationships of this schema
     * @return AbstractSchemaRelationship[]
     */
    final public function getSchemaRelationships(): array
    {
        return $this->relationships;
    }

    /**
     * Extracts the resource ID based on identifier property name
     * @param $object
     * @return mixed
     */
    final public function getResourceId($object)
    {
        return $object->{'get' . ucfirst($this->getIdentifierPropertyName())}();
    }

    /**
     * Add a SchemaAttribute
     * If an attribute in the array with the same key exists, it will be replaced.
     * @param $attribute SchemaAttribute|array $attribute   If argument is an array, it must be compatible
     *                                                      with SchemaAttribute specifications array.
     * @throws InvalidArgumentException
     */
    private function addSchemaAttribute($attribute): void
    {
        if(!$attribute instanceof SchemaAttribute) {
            if(is_array($attribute)) {
                $attribute = SchemaAttribute::fromArray($attribute);
            } else {
                // Must be a SchemaAttribute instance or a compatible array
                throw InvalidArgumentException::fromResourceSchemaAddSchemaAttribute();
            }
        }

        // Add to the attributes array with the key as index
        $this->attributes[$attribute->getKey()] = $attribute;
    }

    /**
     * Add an AbstractSchemaRelationship
     * If a relationship in the array with the same key exists, it will be replaced.
     * @param AbstractSchemaRelationship|array $relationship    If argument is an array, it must be compatible with
     *                                                          the AbstractSchemaRelationship specifications array
     * @throws InvalidArgumentException
     * @throws InvalidSpecificationException
     */
    private function addSchemaRelationship($relationship): void
    {
        if(!$relationship instanceof AbstractSchemaRelationship) {
            if(is_array($relationship)) {
                if(!isset($relationship["bindType"]) || !is_string($relationship["bindType"])) {
                    throw new InvalidSpecificationException("Index 'bindType' is required");
                }

                if(!isset($relationship["options"]) && !is_array($relationship['options'])) {
                    throw new InvalidSpecificationException("Index 'options' must be a compatible array");
                }

                $bindType = $relationship['bindType'];
                $options = $relationship['options'];

                if(!is_subclass_of($bindType, AbstractSchemaRelationship::class)) {
                    throw new InvalidSpecificationException("Index 'bindType' must be a class that extends ".
                        "AbstractSchemaRelationship");
                }

                /** @var AbstractSchemaRelationship $relationship */
                $relationship = new $bindType;
                $relationship->setOptions($options);
            } else {
                throw InvalidArgumentException::fromResourceSchemaAddRelationship();
            }
        }

        // Add to the relationships array with the key as index
        $this->relationships[$relationship->getKey()] = $relationship;
    }
}