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


interface ResourceSchemaInterface
{
    /**
     * Returns the resource type used by this schema
     *
     * @return string
     */
    public function getResourceType(): ?string;

    /**
     * Returns the FQCN of the object to map the JSON API resource
     *
     * @return string
     */
    public function getMappingClass(): ?string;

    /**
     * Returns the identifier specifications of the schema
     *
     * @return IdentifierInterface
     */
    public function getIdentifier(): IdentifierInterface;

    /**
     * Returns the attributes of this schema
     *
     * @return AttributeInterface[]
     */
    public function getAttributes(): array;

    /**
     * Returns the relationships of this schema
     *
     * @return array
     */
    public function getRelationships(): array;

    /**
     * Returns the meta items of this schema
     *
     * @return MetaInterface[]
     */
    public function getMeta(): array;
}