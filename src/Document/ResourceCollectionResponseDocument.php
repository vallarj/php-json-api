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

class ResourceCollectionResponseDocument extends AbstractResponseDocument
{
    /** @var array The object bound to the document */
    private $boundObjects;

    /**
     * Adds a resource object to the document
     * @param $object
     * @throws InvalidArgumentException
     */
    public function addResource($object): void
    {
        if (is_object($object)) {
            $this->boundObjects[] = $object;
        } else {
            throw InvalidArgumentException::fromResourceCollectionResponseDocumentAddResource();
        }
    }

    /**
     * @inheritdoc
     */
    public function getData(): array
    {
        // Return empty array if no bound objects
        if (empty($this->boundObjects)) {
            return [];
        }

        $data = [];
        $included = [];
        foreach($this->boundObjects as $boundObject) {
            // Return empty array if no resource schema found
            if (!$this->hasPrimarySchema(get_class($boundObject))) {
                continue;
            }

            // Extract the document components (i.e., single resource data and included)
            list($resource, $included) = $this->extractDocumentComponents($boundObject, $included);

            // Push resource data into array of resources
            $data[] = $resource;
        }

        // Build the root document object
        $root = [
            "data" => $data
        ];

        if (!empty($included)) {
            // For each included item, disassemble the array
            foreach ($included as $items) {
                // First level: relationship types
                foreach ($items as $item) {
                    // Second level: relationship ids

                    $root['included'][] = $item;
                }
            }
        }

        // Return the root document object
        return $root;
    }
}