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
use Vallarj\JsonApi\Schema\SchemaAttribute;
use Vallarj\JsonApi\Schema\SchemaRelationship;

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
     * Gets a JSON API equivalent array
     * @return array
     */
    public function getData(): array
    {
        // Return empty array if no bound object
        if(!$this->boundObject) {
            return [];
        }

        // Find a compatible ResourceSchema for the bound object.
        $resourceSchema = $this->getResourceSchema(get_class($this->boundObject));

        // Return empty array if no resource schema found
        if(!$resourceSchema) {
            return [];
        }

        // Extract attributes
        $attributes = $this->extractAttributes($resourceSchema->getAttributes(), $this->boundObject);

        // Extract relationships
        $relationships = $this->extractRelationships($resourceSchema->getRelationships(), $this->boundObject);

        // Build the return object

        $data = [
            "type" => $resourceSchema->getType(),
            "id" => $this->boundObject->{'get' . ucfirst($resourceSchema->getIdentifierPropertyName())}(),
            "attributes" => $attributes,
        ];

        // Include relationships if not empty
        if(!empty($relationships['relationships'])) {
            $data['relationships'] = $relationships['relationships'];
        }

        $root = [
            "data" => $data
        ];

        if(!empty($relationships['included'])) {
            $included = $relationships['included'];

            // For each included item, disassemble the array
            foreach($included as $relType => $items) {
                // First level: relationship types
                foreach($items as $id => $item) {
                    // Second level: relationship ids

                    $root['included'][] = $item;
                }
            }
        }

        return $root;
    }

    /**
     * Extract the attributes into an array using the specified SchemaAttributes
     * @param SchemaAttribute[] $schemaAttributes
     * @param $object
     * @return array
     */
    private function extractAttributes(array $schemaAttributes, $object): array
    {
        $attributes = [];

        foreach($schemaAttributes as $schemaAttribute) {
            $key = $schemaAttribute->getKey();
            $attributes[$key] = $object->{'get' . ucfirst($key)}();
        }

        return $attributes;
    }

    /**
     * Extract the relationships and included resources into an array using the specified SchemaRelationships
     * @param SchemaRelationship[] $schemaRelationships
     * @param $object
     * @return array
     */
    private function extractRelationships(array $schemaRelationships, $object): array
    {
        $relationships = [];
        // A 2d array with relationship key as row index and relationship id as column index
        $included = [];

        foreach($schemaRelationships as $schemaRelationship) {
            $key = $schemaRelationship->getKey();
            $relationshipObject = $object->{'get' . ucfirst($key)}();

            // If a To-One relationship
            if($schemaRelationship->getCardinality() === SchemaRelationship::TO_ONE) {
                // if relationshipObject is null, set relationship data as null
                if(is_null($relationshipObject)) {
                    $relationships[$key] = [
                        "data" => null
                    ];

                    continue;
                }

                // Get the compatible ResourceSchema for this object
                $schema = $schemaRelationship->getSchema(get_class($relationshipObject));

                if(!is_null($schema)) {
                    // Get the type and ID of the relationship
                    $relType = $schema->getType();
                    $relId = $relationshipObject->{'get' . ucfirst($schema->getIdentifierPropertyName())}();

                    // Add relationship into the relationships array
                    $relationships[$key] = [
                        "data" => [
                            "type" => $relType,
                            "id" => $relId
                        ],
                    ];

                    // If relationship is included
                    if($schemaRelationship->isIncluded()) {
                        $relAttributes = $this->extractAttributes($schema->getAttributes(), $relationshipObject);

                        $included[$relType][$relId] = [
                            "type" => $relType,
                            "id" => $relId,
                            "attributes" => $relAttributes
                        ];
                    }
                }
            } else if($schemaRelationship->getCardinality() === SchemaRelationship::TO_MANY) {
                // Relationship object is assumed to be an array
                foreach($relationshipObject as $item) {
                    // Get the compatible ResourceSchema for this item
                    $schema = $schemaRelationship->getSchema(get_class($item));

                    // Continue if no compatible schema
                    if(!$schema) {
                        continue;
                    }

                    $relationships[$key][] = [
                        "data" => [
                            "type" => $schema->getType(),
                            "id" => $item->{'get' . ucfirst($schema->getIdentifierPropertyName())}()
                        ],
                    ];
                }
            }
        }

        return [
            "relationships" => $relationships,
            "included" => $included
        ];
    }
}