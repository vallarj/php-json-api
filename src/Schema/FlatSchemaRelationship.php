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


class FlatSchemaRelationship extends AbstractSchemaRelationship
{
    /** @var string Specifies the relationship key */
    private $key;

    /** @var string Specifies the expected type */
    private $type;

    /** @var string Specifies the property name of relationship id */
    private $mappedAs;

    function __construct()
    {
        parent::__construct();

        $this->key = "";
        $this->type = "";
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

        if(isset($options['cardinality'])) {
            $this->setCardinality($options['cardinality']);
        }

        if(isset($options['type'])) {
            $this->type = $options['type'];
        }

        if(isset($options['mappedAs'])) {
            $this->mappedAs = $options['mappedAs'];
        }
    }

    /**
     * @inheritdoc
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @inheritdoc
     */
    public function isIncluded(): bool
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function getRelationship($parentObject): array
    {
        $relationship = $this->getMappedObject($parentObject);

        if($this->getCardinality() === self::TO_ONE) {
            if(!$relationship) {
                $data = null;
            } else {
                $data = [
                    "type" => $this->type,
                    "id" => $relationship
                ];
            }
        } else if($this->getCardinality() === self::TO_MANY) {
            $data = [];
            foreach($relationship as $item) {
                if(!$item) {
                    continue;
                } else {
                    $data[] = [
                        "type" => $this->type,
                        "id" => $item
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
        return $parentObject->{'get' . ucfirst($this->mappedAs)}();
    }

    /**
     * @inheritdoc
     */
    protected function setToOneRelationship($parentObject, string $type, string $id): bool
    {
        // Check if same type
        if($type !== $this->type) {
            return false;
        }

        // Set relationship ID in parent object
        $parentObject->{'set' . ucfirst($this->mappedAs)}($id);

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
        // Check if valid type
        if($type !== $this->type) {
            return false;
        }

        // Add relationship ID in parent object
        $parentObject->{'add' . ucfirst($this->mappedAs)}($id);

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function clearToManyRelationship($parentObject): bool
    {
        // Get current relationship IDs (expects an array)
        $ids = $this->getMappedObject($parentObject);

        // Assumes parent object has a 'removeRelationship' method
        foreach($ids as $id) {
            $parentObject->{'remove' . ucfirst($this->mappedAs)}($id);
        }

        return true;
    }
}