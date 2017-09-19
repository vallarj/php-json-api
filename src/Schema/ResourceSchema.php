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
    /** @var SchemaAttribute[] Attributes of this schema */
    private $attributes;

    /** @var AbstractSchemaRelationship[] Relationships of this schema */
    private $relationships;

    /** @var string The property name of the object's identifier*/
    private $identifierPropertyName;

    /**
     * ResourceSchema constructor.
     * @param array $resourceSpecifications Specifications of the ResourceSchema
     */
    function __construct(array $resourceSpecifications = [])
    {
        $this->attributes = [];
        $this->relationships = [];
        $this->identifierPropertyName = "id";

        // Create attributes
        if(isset($resourceSpecifications['attributes']) && is_array($resourceSpecifications['attributes'])) {
            $attributeSpecs = $resourceSpecifications['attributes'];

            // Create an attribute for each spec given
            foreach($attributeSpecs as $item) {
                $this->addSchemaAttribute($item);
            }
        }

        // Create relationships
        if(isset($resourceSpecifications['relationships']) && is_array($resourceSpecifications['relationships'])) {
            $relationshipSpecs = $resourceSpecifications['relationships'];

            // Create a relationship for each spec given
            foreach($relationshipSpecs as $item) {
                $this->addSchemaRelationship($item);
            }
        }

        if(isset($resourceSpecifications['identifier'])) {
            $this->setIdentifierPropertyName($resourceSpecifications['identifier']);
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
     * Sets the identifier property name of the object to bind
     * @param string $name
     */
    public function setIdentifierPropertyName(string $name)
    {
        $this->identifierPropertyName = $name;
    }

    /**
     * Extracts the resource ID based on identifier property name
     * @param $object
     * @return mixed
     */
    public function getResourceId($object)
    {
        return $object->{'get' . ucfirst($this->getIdentifierPropertyName())}();
    }

    /**
     * Sets the resource ID based on identifier property name
     * @param $object
     * @param $id
     */
    public function setResourceId($object, $id): void
    {
        $object->{'set' . ucfirst($this->getIdentifierPropertyName())}($id);
    }

    /**
     * Extract the resource attributes from the given object
     * @param mixed $object     The object to extract the attributes from
     * @return array            Key-value pair of the resource attributes
     */
    public function getResourceAttributes($object): array
    {
        $attributes = [];

        foreach ($this->attributes as $schemaAttribute) {
            $key = $schemaAttribute->getKey();
            $attributes[$key] = $schemaAttribute->getValue($object);
        }

        return $attributes;
    }

    /**
     * @return SchemaAttribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Set the mapped attribute property of the object.
     * Returns true if attribute successfully set
     * @param mixed $object     The object to change the property
     * @param string $key       The attribute key
     * @param mixed $value      The value to set
     * @return bool             True if attribute is successfully set
     */
    public function setResourceAttribute($object, string $key, $value): bool
    {
        if(!isset($this->attributes[$key])) {
            return false;
        }

        $schemaAttribute = $this->attributes[$key];
        $schemaAttribute->setValue($object, $value);
        return true;
    }

    /**
     * Add a SchemaAttribute to the ResourceSchema.
     * If an attribute in the array with the same key exists, it will be replaced.
     * @param SchemaAttribute|array $attribute  If argument is an array, it must be compatible
     *                                                  with the SchemaAttribute builder specifications.
     * @throws InvalidArgumentException
     */
    public function addSchemaAttribute($attribute): void
    {
        if($attribute instanceof SchemaAttribute) {
            $this->attributes[$attribute->getKey()] = $attribute;
        } else if(is_array($attribute)) {
            $attribute = SchemaAttribute::fromArray($attribute);

            // Add to the attributes array with the key as index.
            $this->attributes[$attribute->getKey()] = $attribute;
        } else {
            // Must be a SchemaAttribute instance or a compatible array
            throw InvalidArgumentException::fromResourceSchemaAddSchemaAttribute();
        }
    }

    /**
     * @return AbstractSchemaRelationship[]
     */
    public function getRelationships(): array
    {
        return $this->relationships;
    }

    /**
     * Extract the resource relationships from the given object
     * @param mixed $object     The object to extract the relationships from
     * @return array            Array containing the relationships
     */
    public function getResourceRelationships($object): array
    {
        $relationships = [];

        foreach($this->relationships as $schemaRelationship) {
            $key = $schemaRelationship->getKey();
            $relationships[$key] = $schemaRelationship->getRelationship($object);
        }

        return $relationships;
    }

    /**
     * Set the mapped relationship property of the object
     * Returns true if relationship is successfully set
     * @param mixed $object         The object to change the property
     * @param string $key           The relationship key
     * @param array $relationship   Must be a compatible array
     * @return bool
     */
    public function setResourceRelationship($object, string $key, ?array $relationship): bool
    {
        if(!isset($this->relationships[$key])) {
            return false;
        }

        $schemaRelationship = $this->relationships[$key];
        return $schemaRelationship->setRelationship($object, $relationship);
    }

    /**
     * Adds an AbstractSchemaRelationship to the ResourceSchema.
     * If a relationship in the array with the same key exists, it will be replaced.
     * @param AbstractSchemaRelationship|array $relationship    If argument is an array, it must be compatible with
     *                                                          the NestedSchemaRelationship builder specifications
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

            else InvalidArgumentException::fromResourceSchemaAddRelationship();
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