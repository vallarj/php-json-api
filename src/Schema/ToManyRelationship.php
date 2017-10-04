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

class ToManyRelationship implements ToManyRelationshipInterface
{
    /** @var string Specifies the key of the relationship */
    private $key = "";

    /** @var string Specifies the property name of the mapped relationship */
    private $mappedAs = "";

    /** @var string[] Array of expected AbstractResourceSchema FQCNs */
    private $expectedSchemas = [];

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

        if(isset($options['expects'])) {
            $expects = $options['expects'];

            if(!is_array($expects)) {
                throw new InvalidSpecificationException("Index 'expects' must be a compatible array");
            }

            $this->expectedSchemas = $expects;
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
    public function getExpectedSchemas(): array
    {
        return $this->expectedSchemas;
    }

    /**
     * @inheritdoc
     */
    public function getCollection($parentObject): array
    {
        return $parentObject->{'get' . ucfirst($this->mappedAs)}();
    }

    /**
     * @inheritdoc
     */
    public function addItem($parentObject, $object): void
    {
        $parentObject->{'add' . ucfirst($this->mappedAs)}($object);
    }
}