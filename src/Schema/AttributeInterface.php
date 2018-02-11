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


interface AttributeInterface
{
    /**
     * Set options of this specification
     * @param array $options    Array that contains the options for this specification
     */
    public function setOptions(array $options): void;

    /**
     * Returns the attribute key.
     * @return string
     */
    public function getKey(): string;

    /**
     * Returns the value of the attribute
     * @param mixed $parentObject   The target object to get the attribute value from
     * @return mixed                The value of the attribute
     */
    public function getValue($parentObject);

    /**
     * Sets the value of the attribute
     * @param mixed $parentObject   The target object in which the attribute value is to be set
     * @param $value
     */
    public function setValue($parentObject, $value): void;

    /**
     * Returns true if attribute is readable
     * @return bool
     */
    public function isReadable(): bool;

    /**
     * Returns true if attributes is writable
     * @return bool
     */
    public function isWritable(): bool;

    /**
     * Returns a pre-processed value of the input value
     * @param $value
     * @return mixed
     */
    public function filterValue($value);

    /**
     * Returns ValidationResultInterface that represents the result of the validation
     * @param mixed $value
     * @param array $context    The context this attribute belongs to
     * @return ValidationResultInterface
     */
    public function isValid($value, $context): ValidationResultInterface;

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