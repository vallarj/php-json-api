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

namespace Vallarj\JsonApi\Service\Options;


use Vallarj\JsonApi\Exception\InvalidConfigurationException;
use Vallarj\JsonApi\Schema\ResourceSchema;

class SchemaOptions
{
    private $schemas;

    function __construct(array $options)
    {
        $this->schemas = [];

        foreach($options as $schema => $option) {
            if(!isset($option['type']) || !isset($option['class'])) {
                throw new InvalidConfigurationException("Keys 'type' and 'class' are required for schema configuration.");
            }

            $this->schemas[$schema] = [
                'type' => $option['type'],
                'class' => $option['class']
            ];
        }
    }

    public function hasClassCompatibleSchema(string $schemaClass, string $resourceClass): bool
    {
        if(!$this->hasCompatibleSchema($schemaClass)) {
            return false;
        }

        return $this->schemas[$schemaClass]['class'] === $resourceClass;
    }

    public function hasTypeCompatibleSchema(string $schemaClass, string $resourceType): bool
    {
        if(!$this->hasCompatibleSchema($schemaClass)) {
            return false;
        }

        return $this->schemas[$schemaClass]['type'] === $resourceType;
    }

    public function getResourceClassBySchema(string $schemaClass): ?string
    {
        if(isset($this->schemas[$schemaClass])) {
            return $this->schemas[$schemaClass]['class'];
        }

        return null;
    }

    public function getResourceTypeBySchema(string $schemaClass): ?string
    {
        if(isset($this->schemas[$schemaClass])) {
            return $this->schemas[$schemaClass]['type'];
        }

        return null;
    }

    private function hasCompatibleSchema(string $schemaClass): bool
    {
        return isset($this->schemas[$schemaClass]) && is_subclass_of($schemaClass, ResourceSchema::class);
    }
}