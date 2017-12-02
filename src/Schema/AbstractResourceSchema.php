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

abstract class AbstractResourceSchema implements ResourceSchemaInterface
{
    /** @var string Specifies the resource type */
    protected $resourceType;

    /** @var string Specifies the FQCN of the object to map the JSON API resource */
    protected $mappingClass;

    /** @var IdentifierInterface The identifier of this schema */
    private $identifier;

    /** @var Attribute[] Attributes of this schema */
    private $attributes = [];

    /** @var array Relationships of this schema */
    private $relationships = [];

    /**
     * @inheritdoc
     */
    final public function getResourceType(): ?string
    {
        return $this->resourceType;
    }

    /**
     * @inheritdoc
     */
    final public function getMappingClass(): ?string
    {
        return $this->mappingClass;
    }

    /**
     * @inheritdoc
     */
    final public function getIdentifier(): IdentifierInterface
    {
        if(is_null($this->identifier)) {
            $this->identifier = new Identifier();
        }

        return $this->identifier;
    }

    /**
     * Sets the Identifier
     *
     * @param IdentifierInterface| array $identifier
     * @throws InvalidArgumentException if argument is incompatible
     * @throws InvalidSpecificationException if type is missing
     */
    final public function setIdentifier($identifier)
    {
        if(!$identifier instanceof IdentifierInterface) {
            if(is_array($identifier)) {
                if(!isset($identifier['type']) || !is_string($identifier['type'])) {
                    throw new InvalidSpecificationException("Index 'type' is required");
                }

                $type = $identifier['type'];
                $options = $identifier['options'] ?? null;

                if(!is_subclass_of($type, IdentifierInterface::class)) {
                    throw new InvalidSpecificationException("Index 'type' must be a class that implements " .
                        "IdentifierInterface");
                }

                /** @var IdentifierInterface $identifier */
                $identifier = new $type;

                if($options) {
                    if(!is_array($options)) {
                        throw new InvalidSpecificationException("Index 'options' must be a compatible array");
                    }

                    $identifier->setOptions($options);
                }
            } else {
                // Must be a AttributeInterface instance or a compatible array
                throw new InvalidArgumentException("Argument must be an instance of IdentifierInterface or an array " .
                    "compatible with schema identifier builder specifications");
            }
        }

        // Set identifier
        $this->identifier = $identifier;
    }

    /**
     * @inheritdoc
     */
    final public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Add a Attribute
     * If an attribute in the array with the same key exists, it will be replaced.
     * @param $attribute AttributeInterface|array $attribute   If argument is an array, it must be compatible
     *                                                      with Attribute specifications array.
     * @throws InvalidArgumentException
     * @throws InvalidSpecificationException
     */
    final public function addAttribute($attribute): void
    {
        if(!$attribute instanceof AttributeInterface) {
            if(is_array($attribute)) {
                if(!isset($attribute['type']) || !is_string($attribute['type'])) {
                    throw new InvalidSpecificationException("Index 'type' is required");
                }

                $type = $attribute['type'];
                $options = $attribute['options'] ?? null;

                if(!is_subclass_of($type, AttributeInterface::class)) {
                    throw new InvalidSpecificationException("Index 'type' must be a class that implements " .
                        "AttributeInterface");
                }

                /** @var AttributeInterface $attribute */
                $attribute = new $type;

                if($options) {
                    if(!is_array($options)) {
                        throw new InvalidSpecificationException("Index 'options' must be a compatible array");
                    }

                    $attribute->setOptions($options);
                }
            } else {
                // Must be a AttributeInterface instance or a compatible array
                throw new InvalidArgumentException("Argument must be an instance of AttributeInterface or an array " .
                    "compatible with schema attribute builder specifications");
            }
        }

        // Add to the attributes array with the key as index
        $this->attributes[$attribute->getKey()] = $attribute;
    }

    /**
     * @inheritdoc
     */
    final public function getRelationships(): array
    {
        return $this->relationships;
    }

    /**
     * Add a relationship specification
     * If a relationship in the array with the same key exists, it will be replaced.
     * @param ToOneRelationshipInterface|ToManyRelationshipInterface|array $relationship
     *                                                          If argument is an array, it must be compatible with
     *                                                          the AbstractRelationship specifications array
     * @throws InvalidArgumentException
     * @throws InvalidSpecificationException
     */
    final public function addRelationship($relationship): void
    {
        if(!$relationship instanceof ToOneRelationshipInterface &&
            !$relationship instanceof ToManyRelationshipInterface) {
            if(is_array($relationship)) {
                if(!isset($relationship["type"]) || !is_string($relationship["type"])) {
                    throw new InvalidSpecificationException("Index 'type' is required");
                }

                $type = $relationship['type'];
                $options = $relationship['options'] ?? null;

                if(!is_subclass_of($type, ToOneRelationshipInterface::class) &&
                    !is_subclass_of($type, ToManyRelationshipInterface::class)) {
                    throw new InvalidSpecificationException("Index 'type' must be a class that implements " .
                        "ToOneRelationshipInterface or ToManyRelationshipInterface");
                }

                /** @var ToOneRelationshipInterface|ToManyRelationshipInterface $relationship */
                $relationship = new $type;

                if($options) {
                    if(!is_array($options)) {
                        throw new InvalidSpecificationException("Index 'options' must be a compatible array");
                    }

                    $relationship->setOptions($options);
                }
            } else {
                throw new InvalidArgumentException("Argument must be an instance of ToOneRelationshipInterface, " .
                    "ToManyRelationshipInterface, or an array " .
                    "compatible with schema relationship specifications");
            }
        }

        // Add to the relationships array with the key as index
        $this->relationships[$relationship->getKey()] = $relationship;
    }
}