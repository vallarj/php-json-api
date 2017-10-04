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

    /** @var Attribute[] Attributes of this schema */
    private $attributes = [];

    /** @var AbstractRelationship[] Relationships of this schema */
    private $relationships = [];

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
     * Extracts the resource ID based on identifier property name
     * @param $object
     * @return mixed
     */
    final public function getResourceId($object)
    {
        return $object->{'get' . ucfirst($this->getIdentifierPropertyName())}();
    }

    /**
     * Sets the resource ID based on identifier property name
     * @param $object
     * @param mixed $id
     */
    final public function setResourceId($object, $id): void
    {
        $object->{'set' . ucfirst($this->getIdentifierPropertyName())}($id);
    }

    /**
     * Returns the SchemaAttributes of this schema
     * @return Attribute[]
     */
    final public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Add a Attribute
     * If an attribute in the array with the same key exists, it will be replaced.
     * @param $attribute Attribute|array $attribute   If argument is an array, it must be compatible
     *                                                      with Attribute specifications array.
     * @throws InvalidArgumentException
     */
    final public function addAttribute($attribute): void
    {
        if(!$attribute instanceof Attribute) {
            if(is_array($attribute)) {
                $attribute = Attribute::fromArray($attribute);
            } else {
                // Must be a Attribute instance or a compatible array
                throw InvalidArgumentException::fromResourceSchemaAddSchemaAttribute();
            }
        }

        // Add to the attributes array with the key as index
        $this->attributes[$attribute->getKey()] = $attribute;
    }

    /**
     * Returns the AbstractSchemaRelationships of this schema
     * @return AbstractRelationship[]
     */
    final public function getRelationships(): array
    {
        return $this->relationships;
    }

    /**
     * Add an AbstractRelationship
     * If a relationship in the array with the same key exists, it will be replaced.
     * @param AbstractRelationship|array $relationship    If argument is an array, it must be compatible with
     *                                                          the AbstractRelationship specifications array
     * @throws InvalidArgumentException
     * @throws InvalidSpecificationException
     */
    final public function addRelationship($relationship): void
    {
        if(!$relationship instanceof AbstractRelationship) {
            if(is_array($relationship)) {
                if(!isset($relationship["bindType"]) || !is_string($relationship["bindType"])) {
                    throw new InvalidSpecificationException("Index 'bindType' is required");
                }

                // TODO: Options must be optional
                if(!isset($relationship["options"]) && !is_array($relationship['options'])) {
                    throw new InvalidSpecificationException("Index 'options' must be a compatible array");
                }

                $bindType = $relationship['bindType'];
                $options = $relationship['options'];

                if(!is_subclass_of($bindType, AbstractRelationship::class)) {
                    throw new InvalidSpecificationException("Index 'bindType' must be a class that extends ".
                        "AbstractRelationship");
                }

                /** @var AbstractRelationship $relationship */
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