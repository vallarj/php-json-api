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

class NestedRelationship extends AbstractRelationship
{
    /** @var string Specifies the key of the relationship */
    private $key;

    /** @var string Specifies the property name of the mapped relationship */
    private $mappedAs;

    /** @var string[] Array of expected ResourceSchema FQCNs */
    private $expectedSchemas;

    /**
     * NestedRelationship constructor.
     */
    function __construct()
    {
        parent::__construct();

        $this->key = "";
        $this->mappedAs = "";
    }

    /**
     * @inheritdoc
     */
    public function setOptions(array $options)
    {
        if(isset($options['key'])) {
            $this->key = $this->mappedAs = $options['key'];
        }

        if(isset($options['mappedAs'])) {
            $this->mappedAs = $options['mappedAs'];
        }

        if(isset($options['cardinality'])) {
            $this->setCardinality($options['cardinality']);
        }

        if(isset($options['expects'])) {
            $expects = $options['expects'];

            if(!is_array($expects)) {
                throw new InvalidSpecificationException("Index 'expects' must be a compatible array");
            }

            $this->expectedSchemas = $expects;
        }
    }

    /**
     * Gets the key of the relationship
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    public function getExpectedSchemas(): array
    {
        return $this->expectedSchemas;
    }

    /**
     * @inheritdoc
     */
    public function getMappedObject($parentObject)
    {
        return $parentObject->{'get' . ucfirst($this->mappedAs)}();
    }

    /**
     * @inheritdoc
     */
    protected function setToOneRelationship($parentObject, string $type, string $id): bool
    {
        // Get compatible schema
        $schema = $this->getExpectedResourceByType($type);

        if(!$schema) {
            return false;
        }

        // Create compatible object
        $class = $schema->getClass();
        $object = new $class;

        // Set object ID
        $schema->setResourceId($object, $id);

        // Map to parent object relationship
        $parentObject->{'set' . ucfirst($this->mappedAs)}($object);

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function clearToOneRelationship($parentObject): bool
    {
        $parentObject->{'set' . ucfirst($this->mappedAs)}(null);
        return true;
    }

    /**
     * @inheritdoc
     */
    protected function addToManyRelationship($parentObject, string $type, string $id): bool
    {
        // Get compatible schema
        $schema = $this->getExpectedResourceByType($type);

        if(!$schema) {
            return false;
        }

        // Create compatible object
        $class = $schema->getClass();
        $object = new $class;

        // Set object ID
        $schema->setResourceId($object, $id);

        // Add relationship to parent object
        $parentObject->{'add' . ucfirst($this->mappedAs)}($object);

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function clearToManyRelationship($parentObject): bool
    {
        // Get current relationship objects (expects an array)
        $objects = $this->getMappedObject($parentObject);

        foreach($objects as $object) {
            // Assumes object has a 'removeRelationship' method
            $parentObject->{'remove' . ucfirst($this->mappedAs)}($object);
        }

        return true;
    }
}