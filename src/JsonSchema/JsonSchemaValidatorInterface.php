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

namespace Vallarj\JsonApi\JsonSchema;


interface JsonSchemaValidatorInterface
{
    /**
     * Returns true if data, decoded with json_decode, is a compliant JSON API POST document
     * @param \stdClass $data
     * @return bool
     */
    public function isValidPostDocument(\stdClass $data);

    /**
     * Returns true if data, decoded with json_decode, is a compliant JSON API PATCH document
     * @param \stdClass $data
     * @return mixed
     */
    public function isValidPatchDocument(\stdClass $data);

    /**
     * Returns true if data, decoded with json_decode, is a compliant JSON API To-one Relationship document
     * @param \stdClass $data
     * @return mixed
     */
    public function isValidToOneRelationshipDocument(\stdClass $data);

    /**
     * Returns true if data, decoded with json_decode, is a compliant JSON API To-many Relationship document
     * @param \stdClass $data
     * @return mixed
     */
    public function isValidToManyRelationshipDocument(\stdClass $data);
}