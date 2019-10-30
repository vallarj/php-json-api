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

namespace Vallarj\JsonApi;


use Vallarj\JsonApi\Schema\ResourceSchemaInterface;

interface EncoderInterface
{
    /**
     * Encodes an object into a JSON API document
     *
     * @param $resource
     * @param ResourceSchemaInterface[] $schemas
     * @param array $includedKeys
     * @param array $meta
     * @return string
     */
    public function encode($resource, array $schemas, array $includedKeys = [], array $meta = []): string;
}