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

class SingleResourceDocument extends AbstractDocument
{
    /** @var object The object bound to the document */
    private $boundObject;

    /**
     * Binds an object to the document
     * @param $object
     * @throws InvalidArgumentException
     */
    public function bind($object): void
    {
        if(is_object($object)) {
            $this->boundObject = $object;
        } else {
            throw InvalidArgumentException::fromSingleResourceDocumentBind();
        }
    }

    /**
     * @inheritdoc
     */
    public function getData(): array
    {
        // Return empty array if no bound object
        if(!$this->boundObject) {
            return [];
        }

        // Return empty array if no resource schema found
        if(!$this->hasPrimarySchema(get_class($this->boundObject))) {
            return [];
        }

        // Extract the document components (i.e., data and included)
        list($data, $included) = $this->extractDocumentComponents($this->boundObject);

        $root = [
            "data" => $data
        ];

        if(!empty($included)) {
            // For each included item, disassemble the array
            foreach($included as $items) {
                // First level: relationship types
                foreach($items as $item) {
                    // Second level: relationship ids

                    $root['included'][] = $item;
                }
            }
        }

        return $root;
    }
}