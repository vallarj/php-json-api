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

namespace Vallarj\JsonApi;


use Vallarj\JsonApi\Error\ErrorDocument;
use Vallarj\JsonApi\Exception\InvalidFormatException;

interface DecoderInterface
{
    /**
     * Decodes a POST document into a new object from a compatible schema
     * @param string $data
     * @param array $schemaClasses
     * @param array $validators
     * @param bool $allowEphemeralId
     * @return mixed
     * @throws InvalidFormatException
     */
    public function decodePostResource(
        string $data,
        array $schemaClasses,
        array $validators = [],
        bool $allowEphemeralId = false
    );

    /**
     * Decodes a PATCH document into a new object from a compatible schema
     * @param string $data
     * @param array $schemaClasses
     * @param array $validators
     * @return mixed
     * @throws InvalidFormatException
     */
    public function decodePatchResource(
        string $data,
        array $schemaClasses,
        array $validators = []
    );

    /**
     * Decodes a To-one relationship request into a new object from a compatible schema
     * @param string $data
     * @param array $schemaClasses
     * @return mixed
     * @throws InvalidFormatException
     */
    public function decodeToOneRelationshipRequest(
        string $data,
        array $schemaClasses
    );

    /**
     * Decodes a To-many relationship request into new objects from a compatible schema
     * @param string $data
     * @param array $schemaClasses
     * @return mixed
     * @throws InvalidFormatException
     */
    public function decodeToManyRelationshipRequest(
        string $data,
        array $schemaClasses
    );

    /**
     * Check if last operation has validation errors
     * @return bool
     */
    public function hasValidationErrors(): bool;
    /**
     * Return the modified property keys from the last operation
     * @return array
     */
    public function getModifiedProperties(): array;

    /**
     * Returns the error document generated by the last decoding operation
     * @return null|ErrorDocument
     */
    public function getErrorDocument(): ?ErrorDocument;
}