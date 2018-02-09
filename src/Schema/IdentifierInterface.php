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


interface IdentifierInterface
{
    /**
     * Allows implementor to receive options array
     *
     * @param array $options
     * @return mixed
     */
    public function setOptions(array $options);

    /**
     * Must return the identifier key name of the bound object
     *
     * @return string
     */
    public function getIdentifierKey(): string;

    /**
     * Extracts the resource ID based on identifier key
     *
     * @param $object
     * @return mixed
     */
    public function getResourceId($object);

    /**
     * Sets the resource ID based on identifier key
     *
     * @param $object
     * @param mixed $id
     */
    public function setResourceId($object, $id): void;
}