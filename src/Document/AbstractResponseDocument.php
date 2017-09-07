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

namespace Vallarj\JsonApi\Document;


use Vallarj\JsonApi\Exception\InvalidArgumentException;
use Vallarj\JsonApi\Schema\ResponseSchema;

abstract class AbstractResponseDocument
{
    /** @var ResponseSchema[] Array of ResourceSchemas used by the document */
    private $resourceSchemas;

    /**
     * AbstractResponseDocument constructor.
     */
    function __construct()
    {
        $this->resourceSchemas = [];
    }

    /**
     * Returns the ResponseSchema bindable with the given FQCN
     * @param string $class
     * @return null|ResponseSchema
     */
    public function getResourceSchema(string $class): ?ResponseSchema
    {
        if(isset($this->resourceSchemas[$class])) {
            return $this->resourceSchemas[$class];
        }

        return null;
    }

    /**
     * Adds a ResponseSchema to the list of ResourceSchemas that the document can use
     * If a schema in the array with the same class exists, it will be replaced.
     * @param ResponseSchema|array $resourceSchema  If argument is an array, it must be compatible with
     *                                              the ResponseSchema builder specifications
     * @throws InvalidArgumentException
     */
    public function addResourceSchema($resourceSchema): void
    {
        if($resourceSchema instanceof ResponseSchema) {
            $this->resourceSchemas[$resourceSchema->getClass()] = $resourceSchema;
        } else if(is_array($resourceSchema)) {
            $resourceSchema = ResponseSchema::fromArray($resourceSchema);

            // Add to the schemas array with the class as index
            $this->resourceSchemas[$resourceSchema->getClass()] = $resourceSchema;
        } else {
            // Must be a ResponseSchema instance or a compatible array
            throw InvalidArgumentException::fromAbstractDocumentAddRelationship();
        }
    }
}