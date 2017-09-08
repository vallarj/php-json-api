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
use Vallarj\JsonApi\Schema\ResponseSchemaAttribute;
use Vallarj\JsonApi\Schema\ResponseSchemaRelationship;

abstract class AbstractResponseDocument
{
    /** @var ResponseSchema[] Array of ResponseSchemas used by the document */
    private $primarySchemas;

    /** @var ResponseSchema[] Array of ResponseSchemas for included resources */
    private $includedSchemas;

    /**
     * AbstractResponseDocument constructor.
     */
    function __construct()
    {
        $this->primarySchemas = [];
    }

    /**
     * Checks if the document has a registered schema for a given class
     * @param string $class
     * @return bool
     */
    public function hasPrimarySchema(string $class): bool
    {
        return isset($this->primarySchemas[$class]);
    }

    /**
     * Returns a ResponseSchema from the array of primary resource ResponseSchemas for the given class
     * @param string $class
     * @return null|ResponseSchema
     */
    public function getPrimarySchema(string $class): ?ResponseSchema
    {
        if($this->hasPrimarySchema($class)) {
            return $this->primarySchemas[$class];
        }

        return null;
    }

    /**
     * Adds a ResponseSchema to the list of ResponseSchemas that the document can use to bind an object as a
     * primary resource
     * If a schema in the array with the same class exists, it will be replaced.
     * @param ResponseSchema|array $primarySchema  If argument is an array, it must be compatible with
     *                                              the ResponseSchema builder specifications
     * @throws InvalidArgumentException
     */
    public function addPrimarySchema($primarySchema): void
    {
        if($primarySchema instanceof ResponseSchema) {
            $this->primarySchemas[$primarySchema->getClass()] = $primarySchema;
        } else if(is_array($primarySchema)) {
            $primarySchema = ResponseSchema::fromArray($primarySchema);

            // Add to the schemas array with the class as index
            $this->primarySchemas[$primarySchema->getClass()] = $primarySchema;
        } else {
            // Must be a ResponseSchema instance or a compatible array
            throw InvalidArgumentException::fromAbstractResponseDocumentAddSchema();
        }
    }

    /**
     * Returns a ResponseSchema from the array of included resource ResponseSchemas for the given class
     * @param string $class
     * @return null|ResponseSchema
     */
    public function getIncludedSchema(string $class): ?ResponseSchema
    {
        if(isset($this->includedSchemas[$class])) {
            return $this->includedSchemas[$class];
        }

        return null;
    }

    /**
     * Adds a ResponseSchema to the list of ResponseSchemas that the document can use for resource inclusion
     * If a schema in the array with the same class exists, it will be replaced.
     * @param ResponseSchema|array $includedSchema  If argument is an array, it must be compatible with
     *                                              the ResponseSchema builder specifications
     * @throws InvalidArgumentException
     */
    public function addIncludedSchema($includedSchema): void
    {
        if($includedSchema instanceof ResponseSchema) {
            $this->includedSchemas[$includedSchema->getClass()] = $includedSchema;
        } else if(is_array($includedSchema)) {
            $includedSchema = ResponseSchema::fromArray($includedSchema);

            // Add to the schemas array with the class as index
            $this->includedSchemas[$includedSchema->getClass()] = $includedSchema;
        } else {
            // Must be a ResponseSchema instance or a compatible array
            throw InvalidArgumentException::fromAbstractResponseDocumentAddSchema();
        }
    }

    /**
     * Gets a JSON API equivalent array
     * @return array
     */
    abstract public function getData(): array;

    /**
     * Extract the "data" and "included" document components for a single resource.
     * @param $boundObject
     * @param array $included
     * @return array
     */
    final protected function extractDocumentComponents(
        $boundObject,
        array &$included = []
    ): array
    {
        // Find a compatible ResponseSchema for the bound object.
        $resourceSchema = $this->getPrimarySchema(get_class($boundObject));

        // Extract attributes
        $attributes = $this->extractAttributes($resourceSchema->getAttributes(), $boundObject);

        // Extract relationships
        list($relationships, $included) = $this->extractRelationships(
            $resourceSchema->getRelationships(), $boundObject, $included);

        // Build the return data
        $data = [
            "type" => $resourceSchema->getType(),
            "id" => $boundObject->{'get' . ucfirst($resourceSchema->getIdentifierPropertyName())}(),
            "attributes" => $attributes,
        ];

        // Include relationships if not empty
        if (!empty($relationships)) {
            $data['relationships'] = $relationships;
        }

        return array($data, $included);
    }

    /**
     * Extract the attributes into an array using the specified SchemaAttributes
     * @param ResponseSchemaAttribute[] $schemaAttributes
     * @param $object
     * @return array
     */
    private function extractAttributes(array $schemaAttributes, $object): array
    {
        $attributes = [];

        foreach ($schemaAttributes as $schemaAttribute) {
            $key = $schemaAttribute->getKey();
            $attributes[$key] = $object->{'get' . ucfirst($key)}();
        }

        return $attributes;
    }

    /**
     * Extract the relationships and included resources into an array using the specified SchemaRelationships
     * @param ResponseSchemaRelationship[] $schemaRelationships
     * @param $object
     * @param array &$included  Two-dimensional array with relationship key as row index and relationship
     *                          id as column index
     * @return array
     */
    private function extractRelationships(array $schemaRelationships, $object, array &$included = []): array
    {
        $relationships = [];

        foreach ($schemaRelationships as $schemaRelationship) {
            $key = $schemaRelationship->getKey();
            $relationshipObject = $object->{'get' . ucfirst($key)}();

            // If a To-One relationship
            if ($schemaRelationship->getCardinality() === ResponseSchemaRelationship::TO_ONE) {
                // if relationshipObject is null, set relationship data as null
                if (is_null($relationshipObject)) {
                    $relationships[$key] = ["data" => null];
                    continue;
                }

                // Get the compatible ResourceIdentifierSchema for this object
                $resourceIdentifier = $schemaRelationship->getExpectedResourceByClassName(
                    get_class($relationshipObject));

                if (!is_null($resourceIdentifier)) {
                    // Get the type and ID of the relationship
                    $relType = $resourceIdentifier->getType();
                    $relId = $relationshipObject->{'get' . ucfirst($resourceIdentifier->getIdentifierPropertyName())}();

                    // Add relationship into the relationships array
                    $relationships[$key] = [
                        "data" => [
                            "type" => $relType,
                            "id" => $relId
                        ],
                    ];

                    // If relationship is included
                    if ($schemaRelationship->isIncluded()) {
                        $included = $this->includeRelationship($relationshipObject, $included, $relType, $relId);
                    }
                } else {
                    $relationships[$key] = ["data" => null];
                    continue;
                }
            } else if ($schemaRelationship->getCardinality() === ResponseSchemaRelationship::TO_MANY) {
                if (empty($relationshipObject)) {
                    $relationships[$key] = ["data" => []];
                    continue;
                }

                // Relationship object is assumed to be an array
                foreach ($relationshipObject as $item) {
                    // Get the compatible ResourceIdentifierSchema for this object
                    $resourceIdentifier = $schemaRelationship->getExpectedResourceByClassName(
                        get_class($item));

                    if (!is_null($resourceIdentifier)) {
                        // Get the type and ID of the relationship
                        $relType = $resourceIdentifier->getType();
                        $relId = $item->{'get' . ucfirst($resourceIdentifier->getIdentifierPropertyName())}();

                        // Add relationship into the relationships array
                        $relationships[$key]['data'][] = [
                            "type" => $relType,
                            "id" => $relId
                        ];

                        // If relationship is included
                        if ($schemaRelationship->isIncluded()) {
                            $included = $this->includeRelationship($item, $included, $relType, $relId);
                        }
                    } else {
                        $relationships[$key] = ["data" => []];
                        continue;
                    }
                }
            }
        }

        return [$relationships, $included];
    }

    /**
     * Add the relationship resource into the included array.
     * @param $relationshipObject
     * @param array &$included  The source included array
     * @param string $relType   Specifies the resource type
     * @param mixed $relId      Specifies the resource ID
     * @return array            The modified array with the newly added resource
     */
    private function includeRelationship($relationshipObject, array &$included, string $relType, $relId): array
    {
        // Get schema from included schemas
        $schema = $this->getIncludedSchema(get_class($relationshipObject));

        if (!is_null($schema)) {
            $relAttributes = $this->extractAttributes($schema->getAttributes(), $relationshipObject);
            list($relationships, $included) = $this->extractRelationships($schema->getRelationships(),
                $relationshipObject, $included);

            // Include only once
            if (!isset($included[$relType][$relId])) {
                $included[$relType][$relId] = [
                    "type" => $relType,
                    "id" => $relId,
                    "attributes" => $relAttributes,
                ];

                if (!empty($relationships)) {
                    $included[$relType][$relId]['relationships'] = $relationships;
                }
            }
        }
        return $included;
    }
}