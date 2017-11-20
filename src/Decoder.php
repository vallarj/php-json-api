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


use Vallarj\JsonApi\Error\Error;
use Vallarj\JsonApi\Error\ErrorDocument;
use Vallarj\JsonApi\Error\Source\AttributePointer;
use Vallarj\JsonApi\Error\Source\RelationshipPointer;
use Vallarj\JsonApi\Exception\InvalidFormatException;
use Vallarj\JsonApi\JsonSchema\JsonSchemaValidator;
use Vallarj\JsonApi\JsonSchema\JsonSchemaValidatorInterface;
use Vallarj\JsonApi\Schema\AbstractResourceSchema;
use Vallarj\JsonApi\Schema\AttributeInterface;
use Vallarj\JsonApi\Schema\ToManyRelationshipInterface;
use Vallarj\JsonApi\Schema\ToOneRelationshipInterface;

class Decoder implements DecoderInterface
{
    /** @var SchemaManagerInterface Handles resource schema instances */
    private $schemaManager;

    /** @var array  Cache of instantiated objects */
    private $objectCache;

    /**
     * Context of the current operation
     * Keys:
     *  - id: Included only if decodePost or decodePatch operation
     *  - attributes: Attribute values indexed by attribute key
     *  - relationships: Array of relationship type and ids
     *  - modified: Array of modified property keys
     * @var array
     */
    private $context;

    /** @var Error[]  Errors of the last decoding operation */
    private $errors;

    /** @var JsonSchemaValidatorInterface   JsonSchema Validator */
    private $jsonSchemaValidator;

    /**
     * Decoder constructor.
     * @param SchemaManagerInterface $schemaManager
     */
    function __construct(SchemaManagerInterface $schemaManager)
    {
        $this->schemaManager = $schemaManager;
        $this->objectCache = [];

        $this->initialize();
    }

    /**
     * @inheritdoc
     */
    public function decodePostResource(
        string $data,
        array $schemaClasses,
        bool $allowEphemeralId = false
    ) {
        $this->initialize();

        // Decode root object
        $root = json_decode($data);

        // Validate if JSON API document compliant
        if(json_last_error() !== JSON_ERROR_NONE || !$this->getJsonSchemaValidator()->isValidPostDocument($root)) {
            throw new InvalidFormatException("Invalid document format.");
        }

        $data = $root->data;

        // Throw exception if ephemeral IDs are not allowed
        if(property_exists($data, 'id')) {
            if(!$allowEphemeralId) {
                throw new InvalidFormatException("Ephemeral IDs are not allowed.");
            } else {
                $this->context['id'] = $data->id;
            }
        }

        // Do not ignore missing fields
        $resource = $this->decodeSingleResource($data, $schemaClasses, false);

        // Return null if errors occurred
        return $this->hasValidationErrors() ? null : $resource;
    }

    /**
     * @inheritdoc
     */
    public function decodePatchResource(
        string $data,
        array $schemaClasses
    ) {
        $this->initialize();

        // Decode root object
        $root = json_decode($data);

        // Validate if JSON API document compliant
        if(json_last_error() !== JSON_ERROR_NONE || !$this->getJsonSchemaValidator()->isValidPatchDocument($root)) {
            throw new InvalidFormatException("Invalid document format.");
        }

        $data = $root->data;

        // Set context ID
        $this->context['id'] = $data->id;

        // Ignore missing fields
        $resource = $this->decodeSingleResource($data, $schemaClasses, true);

        // Return null if errors occurred
        return $this->hasValidationErrors() ? null : $resource;
    }

    /**
     * @inheritdoc
     */
    public function decodeToOneRelationshipRequest(
        string $data,
        array $schemaClasses
    ) {
        $this->initialize();

        // Decode root object
        $root = json_decode($data);

        // Validate if JSON API document compliant
        if(json_last_error() !== JSON_ERROR_NONE || !$this->getJsonSchemaValidator()->isValidToOneRelationshipDocument($root)) {
            throw new InvalidFormatException("Invalid document format.");
        }

        $data = $root->data;

        $resourceType = $data->type;
        $compatibleSchema = null;

        foreach($schemaClasses as $schemaClass) {
            $schema = $this->schemaManager->get($schemaClass);
            if($schema->getResourceType() == $resourceType) {
                $compatibleSchema = $schema;
                break;
            }
        }

        if(!$compatibleSchema) {
            throw new InvalidFormatException("Invalid 'type' given for this resource");
        }

        return $this->createResourceIdentifier($data, $compatibleSchema);
    }

    /**
     * @inheritdoc
     */
    public function decodeToManyRelationshipRequest(
        string $data,
        array $schemaClasses
    ) {
        $this->initialize();

        // Decode root object
        $root = json_decode($data);

        // Validate if JSON API document compliant
        if(json_last_error() !== JSON_ERROR_NONE || !$this->getJsonSchemaValidator()->isValidToManyRelationshipDocument($root)) {
            throw new InvalidFormatException("Invalid document format.");
        }

        $data = $root->data;

        $collection = [];

        foreach($data as $item) {
            $resourceType = $item->type;
            $compatibleSchema = null;

            foreach($schemaClasses as $schemaClass) {
                $schema = $this->schemaManager->get($schemaClass);
                if($schema->getResourceType() == $resourceType) {
                    $compatibleSchema = $schema;
                    break;
                }
            }

            if(!$compatibleSchema) {
                throw new InvalidFormatException("Invalid 'type' given for this resource");
            }

            $object = $this->createResourceIdentifier($item, $compatibleSchema);

            if($object) {
                $collection[] = $object;
            }
        }

        return $collection;
    }

    /**
     * Check if last operation has validation errors
     * @return bool
     */
    public function hasValidationErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Return the modified property keys from the last operation
     * @return array
     */
    public function getModifiedProperties(): array
    {
        return $this->context['modified'];
    }

    /**
     * Returns the error document generated by the last decoding operation
     * @return null|ErrorDocument
     */
    public function getErrorDocument(): ?ErrorDocument
    {
        if(!empty($this->errors)) {
            $errorDocument = new ErrorDocument("422");
            foreach($this->errors as $error) {
                $errorDocument->addError($error);
            }
            return $errorDocument;
        }

        return null;
    }

    /**
     * Prepare the service for an encoding operation
     */
    private function initialize(): void
    {
        $this->errors = [];
        $this->context = [
            'attributes' => [],
            'relationships' => [],
            'modified' => [],
        ];
    }

    /**
     * Decode a single resource.
     * @param object $data
     * @param array $schemaClasses
     * @param bool $ignoreMissingFields
     * @return mixed
     * @throws InvalidFormatException
     */
    private function decodeSingleResource($data, array $schemaClasses, bool $ignoreMissingFields)
    {
        $resourceType = $data->type;
        $compatibleSchema = null;

        foreach($schemaClasses as $schemaClass) {
            $schema = $this->schemaManager->get($schemaClass);
            if($schema->getResourceType() == $resourceType) {
                $compatibleSchema = $schema;
                break;
            }
        }

        if(!$compatibleSchema) {
            throw new InvalidFormatException("Invalid 'type' given for this resource");
        }

        return $this->createResourceObject($data, $compatibleSchema, $ignoreMissingFields);
    }

    public function addAttributeError(string $attribute, string $detail): void
    {
        $error = new Error();
        $error->setSource(new AttributePointer($attribute));
        $error->setDetail($detail);
        $this->errors[] = $error;
    }

    public function addRelationshipError(string $relationship, string $detail): void
    {
        $error = new Error();
        $error->setSource(new RelationshipPointer($relationship));
        $error->setDetail($detail);
        $this->errors[] = $error;
    }

    private function createResourceIdentifier($data, AbstractResourceSchema $schema)
    {
        $resourceType = $data->type;
        $resourceId = $data->id;

        // Return cached object if already cached
        if(!is_null($resourceId) && isset($this->objectCache[$resourceType][$resourceId])) {
            return $this->objectCache[$resourceType][$resourceId];
        }

        // Return cached object if already cached
        if (!is_null($resourceId) && isset($this->objectCache[$resourceType][$resourceId])) {
            return $this->objectCache[$resourceType][$resourceId];
        }

        // Get the resource class
        $resourceClass = $schema->getMappingClass();

        // Create the object
        $object = new $resourceClass;

        // Set the resource ID
        $schema->setResourceId($object, $resourceId);

        // Return the object
        return $object;
    }

    private function createResourceObject($data, AbstractResourceSchema $schema, bool $ignoreMissingFields)
    {
        $resourceType = $data->type;
        $resourceId = $data->id ?? null;

        // Return cached object if already cached
        if (!is_null($resourceId) && isset($this->objectCache[$resourceType][$resourceId])) {
            return $this->objectCache[$resourceType][$resourceId];
        }

        // Get the resource class
        $resourceClass = $schema->getMappingClass();

        // Create the object
        $object = new $resourceClass;

        // Set the resource ID
        $schema->setResourceId($object, $resourceId);

        // Schema attributes
        $schemaAttributes = $schema->getAttributes();

        // Schema relationships
        $schemaRelationships = $schema->getRelationships();

        // FIRST PASS: Set attributes and relationships to context to prepare for validation
        // This is needed for interdependent validation
        // Set attributes
        if (property_exists($data, 'attributes')) {
            $attributes = $data->attributes;
        } else {
            $attributes = (object)[];
        }

        // Perform attribute pre-processing then add to current context array
        foreach($schemaAttributes as $schemaAttribute) {
            if($schemaAttribute->getAccessType() & AttributeInterface::ACCESS_WRITE) {
                $key = $schemaAttribute->getKey();

                if(property_exists($attributes, $key)) {
                    $value = $attributes->{$key};
                    // Perform attribute pre-processing
                    $value = $schemaAttribute->filterValue($value);

                    // Add to context. This is necessary so that all values will be available if
                    // dependent validation is performed
                    $this->context['attributes'][$key] = $value;
                    $this->context['modified'][] = $key;
                } else {
                    $this->context['attributes'][$key] = null;
                }
            }
        }

        // Set relationships
        if (property_exists($data, 'relationships')) {
            $relationships = $data->relationships;
        } else {
            $relationships = (object)[];
        }

        // Add relationships to current context array
        foreach($schemaRelationships as $schemaRelationship) {
            if($schemaRelationship instanceof ToOneRelationshipInterface &&
                ($schemaRelationship->getAccessType() & ToOneRelationshipInterface::ACCESS_WRITE)) {
                $key = $schemaRelationship->getKey();

                if(property_exists($relationships, $key)) {
                    $this->setToOneRelationshipContext($key, $relationships->{$key});
                    $this->context['modified'][] = $key;
                } else {
                    $this->context['relationships'][$key] = null;
                }
            } else if($schemaRelationship instanceof ToManyRelationshipInterface &&
                ($schemaRelationship->getAccessType() & ToManyRelationshipInterface::ACCESS_WRITE)) {
                $key = $schemaRelationship->getKey();

                if(property_exists($relationships, $key)) {
                    $this->setToManyRelationshipContext($key, $relationships->{$key});
                    $this->context['modified'][] = $key;
                } else {
                    $this->context['relationships'][$key] = [];
                }
            }
        }

        // SECOND PASS: Perform validation and hydrate object using context values
        // Attributes
        foreach($schemaAttributes as $schemaAttribute) {
            if($schemaAttribute->getAccessType() & AttributeInterface::ACCESS_WRITE) {
                $key = $schemaAttribute->getKey();

                $attributeContext = $this->context['attributes'];
                $value = $attributeContext[$key];

                // Null may mean request sent null or request missing attribute
                if(is_null($value)) {
                    // If missing attributes are allowed and attribute is missing, continue
                    if($ignoreMissingFields && !in_array($key, $this->context['modified'])) {
                        continue;
                    }

                    // If attribute is required
                    if($schemaAttribute->isRequired()) {
                        $this->addAttributeError($key, "Field is required.");
                        continue;
                    }

                    // If validateIfEmpty is false
                    if(!$schemaAttribute->validateIfEmpty()) {
                        $schemaAttribute->setValue($object, $value);
                        continue;
                    }
                }

                // Validate attribute
                $validationResult = $schemaAttribute->isValid($value, $this->context);
                if($validationResult->isValid()) {
                    $schemaAttribute->setValue($object, $value);
                } else {
                    $errorMessages = $validationResult->getMessages();
                    foreach($errorMessages as $errorMessage) {
                        $this->addAttributeError($key, $errorMessage);
                    }
                }
            }
        }

        // Relationships
        foreach($schemaRelationships as $schemaRelationship) {
            if($schemaRelationship instanceof ToOneRelationshipInterface &&
                ($schemaRelationship->getAccessType() & ToOneRelationshipInterface::ACCESS_WRITE)) {
                $expectedSchemas = $schemaRelationship->getExpectedSchemas();
                $key = $schemaRelationship->getKey();

                $relationship = $this->context['relationships'][$key];

                // Null may mean request sent null or request missing relationship
                if(is_null($relationship)) {
                    // If missing relationships are allowed and relationship is missing, continue
                    if($ignoreMissingFields && !in_array($key, $this->context['modified'])) {
                        continue;
                    }

                    // If relationship is required
                    if($schemaRelationship->isRequired()) {
                        $this->addRelationshipError($key, "Field is required.");
                        continue;
                    }

                    if(!$schemaRelationship->validateIfEmpty()) {
                        $this->hydrateToOneRelationship($schemaRelationship, $object, null, $expectedSchemas);
                        continue;
                    }
                }

                // Validate relationships
                $validationResult = $schemaRelationship->isValid($relationship['id'], $relationship['type'], $this->context);
                if($validationResult->isValid()) {
                    $this->hydrateToOneRelationship($schemaRelationship, $object, $relationship, $expectedSchemas);
                } else {
                    $errorMessages = $validationResult->getMessages();
                    foreach($errorMessages as $errorMessage) {
                        $this->addRelationshipError($key, $errorMessage);
                    }
                }

            } else if($schemaRelationship instanceof ToManyRelationshipInterface &&
                ($schemaRelationship->getAccessType() & ToManyRelationshipInterface::ACCESS_WRITE)) {
                $expectedSchemas = $schemaRelationship->getExpectedSchemas();
                $key = $schemaRelationship->getKey();

                $relationship = $this->context['relationships'][$key];

                // Empty may mean request sent empty collection or request missing relationship
                if(empty($relationship)) {
                    // If missing relationships are allowed and relationship is missing, continue
                    if($ignoreMissingFields && !in_array($key, $this->context['modified'])) {
                        continue;
                    }

                    // If relationship is required
                    if($schemaRelationship->isRequired()) {
                        $this->addRelationshipError($key, "Field is required.");
                        continue;
                    }

                    if(!$schemaRelationship->validateIfEmpty()) {
                        $this->hydrateToManyRelationship($schemaRelationship, $object, [], $expectedSchemas);
                        continue;
                    }
                }

                // Validate relationships
                $validationResult = $schemaRelationship->isValid($relationship, $this->context);
                if($validationResult->isValid()) {
                    $this->hydrateToManyRelationship($schemaRelationship, $object, $relationship, $expectedSchemas);
                } else {
                    $errorMessages = $validationResult->getMessages();
                    foreach($errorMessages as $errorMessage) {
                        $this->addRelationshipError($key, $errorMessage);
                    }
                }
            }
        }

        return $object;
    }

    private function setToOneRelationshipContext($key, $relationship): void
    {
        $relationshipData = $relationship->data;
        if(!is_null($relationshipData) && !is_object($relationshipData)) {
            throw new InvalidFormatException("Invalid to-one relationship format.");
        }

        $this->context['relationships'][$key] = [
            'type' => $relationshipData->type,
            'id' => $relationshipData->id
        ];
    }

    private function setToManyRelationshipContext($key, $relationship): void
    {
        $relationshipData = $relationship->data;
        if(!is_array($relationshipData)) {
            throw new InvalidFormatException("Invalid to-many relationship format.");
        }

        $relationships = [];
        foreach($relationshipData as $item) {
            $relationships[] = [
                'type' => $item->type,
                'id' => $item->id
            ];
        }

        // Set context
        $this->context['relationships'][$key] = $relationships;
    }

    private function hydrateToOneRelationship(
        ToOneRelationshipInterface $schemaRelationship,
        $parentObject,
        ?array $relationship,
        array $expectedSchemas
    ): bool
    {
        if(is_null($relationship)) {
            $schemaRelationship->clearObject($parentObject);
            return true;
        }

        foreach($expectedSchemas as $schemaClass) {
            $schema = $this->schemaManager->get($schemaClass);
            if($schema->getResourceType() == $relationship['type']) {
                $object = $this->resolveRelationshipObject($schema, $relationship['id']);
                $schemaRelationship->setObject($parentObject, $object);
                return true;
            }
        }

        return false;
    }

    private function hydrateToManyRelationship(
        ToManyRelationshipInterface $schemaRelationship,
        $parentObject,
        array $relationship,
        array $expectedSchemas
    ): bool
    {
        if(empty($relationship)) {
            // Clear collection
            $schemaRelationship->clearCollection($parentObject);
        }

        $modifiedCount = 0;
        foreach($relationship as $item) {
            foreach($expectedSchemas as $schemaClass) {
                $schema = $this->schemaManager->get($schemaClass);
                if($schema->getResourceType() == $item['type']) {
                    $object = $this->resolveRelationshipObject($schema, $item['id']);
                    $schemaRelationship->addItem($parentObject, $object);
                    $modifiedCount++;
                    break;
                }
            }
        }

        return $modifiedCount > 0;
    }

    private function resolveRelationshipObject(AbstractResourceSchema $schema, $id)
    {
        $mappingClass = $schema->getMappingClass();

        if(!isset($this->objectCache[$mappingClass][$id])) {
            $object = new $mappingClass;
            $schema->setResourceId($object, $id);
            $this->objectCache[$mappingClass][$id] = $object;
        }

        return $this->objectCache[$mappingClass][$id];
    }

    public function getJsonSchemaValidator(): JsonSchemaValidatorInterface
    {
        if(!$this->jsonSchemaValidator) {
            $this->jsonSchemaValidator = new JsonSchemaValidator();
        }

        return $this->jsonSchemaValidator;
    }

    public function setJsonSchemaValidator(JsonSchemaValidatorInterface $jsonSchemaValidator)
    {
        $this->jsonSchemaValidator = $jsonSchemaValidator;
    }
}