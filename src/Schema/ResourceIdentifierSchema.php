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


use Vallarj\JsonApi\Exception\InvalidSpecificationException;

class ResourceIdentifierSchema
{
    /** @var string Specifies the resource type */
    private $resourceType;

    /** @var string Specifies the FQCN of the object to bind this schema */
    private $class;

    /** @var string The property name of the object's identifier*/
    private $identifierPropertyName;

    /**
     * ResourceIdentifierSchema constructor.
     * @param string $resourceType
     * @param string $class
     */
    function __construct(string $resourceType, string $class)
    {
        $this->resourceType = $resourceType;
        $this->class = $class;

        // Set defaults
        $this->identifierPropertyName = 'id';
    }

    /**
     * Construct a ResourceSchema from an array compatible
     * with ResourceSchema builder specifications
     * @param array $resourceSpecifications
     * @return ResourceIdentifierSchema
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

        // Create a new instance of ResourceSchema
        $instance = new static($resourceSpecifications['type'], $resourceSpecifications['class']);

        if(isset($resourceSpecifications['identifier'])) {
            $instance->identifierPropertyName = $resourceSpecifications['identifier'];
        }

        // Return the instance
        return $instance;
    }

    /**
     * Gets the type of the resource.
     * @return string
     */
    public function getResourceType(): string
    {
        return $this->resourceType;
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
     * Gets the FQCN of the object bindable to this schema
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
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
     * Gets the identifier property name of the object to bind
     * @return string
     */
    public function getIdentifierPropertyName(): string
    {
        return $this->identifierPropertyName;
    }
}