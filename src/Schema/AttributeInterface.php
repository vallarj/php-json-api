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


interface AttributeInterface
{
    const ACCESS_READ   =   1;
    const ACCESS_WRITE  =   2;

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
     * Returns the access type flag value
     * @return int
     */
    public function getAccessType(): int;
}