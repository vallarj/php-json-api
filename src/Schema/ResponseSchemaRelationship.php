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

class ResponseSchemaRelationship extends AbstractSchemaRelationship
{
    /** @var string Specifies the key of the relationship */
    private $key;

    /** @var bool Specifies if relationship is to be included in the document */
    private $included;

    /** @var ResourceIdentifierSchema[] Array of ResourceIdentifierSchema for each expected class */
    private $expectedResources;

    /**
     * ResponseSchemaRelationship constructor.
     */
    function __construct()
    {
        parent::__construct();

        $this->key = "";
        $this->included = false;
        $this->expectedResources = [];
    }

    /**
     * @inheritdoc
     */
    public function setOptions(array $options)
    {
        if(isset($options['key'])) {
            $this->key = $options['key'];
        }

        if(isset($options['cardinality'])) {
            $this->setCardinality($options['cardinality']);
        }

        $this->included = isset($options['included']) ? $options['included'] : false;

        if(isset($options['expects'])) {
            $expects = $options['expects'];

            if(!is_array($expects)) {
                throw new InvalidSpecificationException("Index 'expects' must be a compatible array.");
            }

            foreach($expects as $resourceIdentifier) {
                $this->addExpectedResource($resourceIdentifier);
            }
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

    /**
     * Checks if this relationship is to be included in the document
     * @return bool
     */
    public function isIncluded(): bool
    {
        return $this->included;
    }

    /**
     * Sets whether or not this relationship is going to be included in the document
     * @param bool $included
     */
    public function setIncluded(bool $included): void
    {
        $this->included = $included;
    }

    /**
     * Add a type mapping for the expected class name
     * Replaces a class mapping if it already exists
     * @param array|ResourceIdentifierSchema $resourceIdentifier
     * @throws InvalidArgumentException
     */
    public function addExpectedResource($resourceIdentifier): void
    {
        if(!$resourceIdentifier instanceof ResourceIdentifierSchema) {
            if(!is_array($resourceIdentifier)) {
                throw InvalidArgumentException::fromResponseSchemaRelationshipAddExpectedResource();
            }

            $resourceIdentifier = ResourceIdentifierSchema::fromArray($resourceIdentifier);
        }

        $this->expectedResources[$resourceIdentifier->getClass()] = $resourceIdentifier;
    }

    /**
     * @inheritdoc
     */
    public function getRelationship($parentObject): array
    {
        $relationship = $this->getMappedObject($parentObject);

        if($this->getCardinality() === self::TO_ONE) {
            if(!$relationship || !isset($this->expectedResources[get_class($relationship)])) {
                $data = null;
            } else {
                $resourceIdentifier = $this->expectedResources[get_class($relationship)];

                $data = [
                    "type" => $resourceIdentifier->getResourceType(),
                    "id" => $resourceIdentifier->getResourceId($relationship)
                ];
            }
        } else if ($this->getCardinality() === self::TO_MANY) {
            $data = [];
            foreach($relationship as $item) {
                if(!$item || !isset($this->expectedResources[get_class($item)])) {
                    continue;
                } else {
                    $resourceIdentifier = $this->expectedResources[get_class($item)];

                    $data[] = [
                        "type" => $resourceIdentifier->getResourceType(),
                        "id" => $resourceIdentifier->getResourceId($item)
                    ];
                }
            }
        } else {
            $data = null;
        }

        return [
            "data" => $data
        ];
    }

    /**
     * @inheritdoc
     */
    public function getMappedObject($parentObject)
    {
        return $parentObject->{'get' . ucfirst($this->getKey())}();
    }
}