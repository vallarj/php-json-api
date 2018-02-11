<?php
/**
 *  Copyright 2017-2018 Justin Dane D. Vallar
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


interface ToManyRelationshipInterface
{
    /**
     * Set options of this specification.
     * @param array $options    Array that contains the options for this specification.
     */
    public function setOptions(array $options): void;

    /**
     * Gets the key of the relationship
     * @return string
     */
    public function getKey(): string;

    /**
     * Returns an array of FQCNs of the expected AbstractResourceSchema
     * @return array
     */
    public function getExpectedSchemas(): array;

    /**
     * Gets the collection of mapped objects
     * @param mixed $parentObject   The parent object to get the relationship collection from
     * @return array
     */
    public function getCollection($parentObject): array;

    /**
     * Adds an object to the collection of mapped objects
     * @param mixed $parentObject   The parent object in which the relationship object will be added
     * @param mixed $object
     */
    public function addItem($parentObject, $object): void;

    /**
     * Clears the collection of mapped object
     * @param $parentObject
     */
    public function clearCollection($parentObject): void;

    /**
     * Returns true if relationship is readable
     * @return bool
     */
    public function isReadable(): bool;

    /**
     * Returns true if relationship is writable
     * @return bool
     */
    public function isWritable(): bool;

    /**
     * Returns ValidationResultInterface that represents the result of the validation
     * @param array $relationships  Array of relationship items, represented by an array with id and type indices
     * @param $context
     * @return ValidationResultInterface
     */
    public function isValid(array $relationships, $context): ValidationResultInterface;

    /**
     * Returns true if validators should be run if value is null
     * @return bool
     */
    public function validateIfEmpty(): bool;

    /**
     * Returns true if attribute is required
     * @return bool
     */
    public function isRequired(): bool;
}