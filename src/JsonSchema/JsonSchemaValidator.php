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

namespace Vallarj\JsonApi\JsonSchema;


use JsonSchema\Validator;

class JsonSchemaValidator implements JsonSchemaValidatorInterface
{
    /**
     * @inheritdoc
     */
    public function isValidPostDocument(\stdClass $data)
    {
        $validator = new Validator;
        $validator->validate($data, $this->getPostSchema());
        return $validator->isValid();
    }

    /**
     * @inheritdoc
     */
    public function isValidPatchDocument(\stdClass $data)
    {
        $validator = new Validator;
        $validator->validate($data, $this->getPatchSchema());
        return $validator->isValid();
    }

    /**
     * @inheritdoc
     */
    public function isValidToOneRelationshipDocument(\stdClass $data)
    {
        $validator = new Validator;
        $validator->validate($data, $this->getToOneRelationshipSchema());
        return $validator->isValid();
    }

    /**
     * @inheritdoc
     */
    public function isValidToManyRelationshipDocument(\stdClass $data)
    {
        $validator = new Validator;
        $validator->validate($data, $this->getToManyRelationshipSchema());
        return $validator->isValid();
    }

    /**
     * Returns the JSON schema for the JSON API POST request document
     * @return \stdClass
     */
    private function getPostSchema(): \stdClass
    {
        return (object) [
            'type' => 'object',
            'properties' => (object) [
                'data' => (object) [
                    '$ref' => '#/definitions/resource',
                ],
            ],
            'definitions' => (object) [
                'resource' => (object) [
                    'type' => 'object',
                    'required' => ['type'],
                    'properties' => (object) [
                        'type' => (object) [
                            'type' => 'string',
                        ],
                        'id' => (object) [
                            'type' => 'string',
                        ],
                        'attributes' => (object) [
                            '$ref' => '#/definitions/attributes',
                        ],
                        'relationships' => (object) [
                            '$ref' => '#/definitions/relationships',
                        ],
                    ],
                    'additionalProperties' => false,
                ],
                'attributes' => (object) [
                    'type' => 'object',
                    'patternProperties' => (object) [
                        '^(?!relationships$|links$|id$|type$)\\w[-\\w_]*$' => (object) [
                            'not' => (object) [
                                'type' => ['object', 'array'],
                            ],
                        ],
                    ],
                    'additionalProperties' => false,
                ],
                'relationships' => (object) [
                    'type' => 'object',
                    'patternProperties' => (object) [
                        '^(?!id$|type$)\\w[-\\w_]*$' => (object) [
                            'type' => 'object',
                            'properties' => (object) [
                                'data' => (object) [
                                    'oneOf' => [
                                        (object) [
                                            '$ref' => '#/definitions/relationshipToOne',
                                        ],
                                        (object) [
                                            '$ref' => '#/definitions/relationshipToMany',
                                        ],
                                    ],
                                ],
                            ],
                            'required' => ['data'],
                            'additionalProperties' => false,
                        ],
                    ],
                    'additionalProperties' => false,
                ],
                'relationshipToOne' => (object) [
                    'anyOf' => [
                        (object) [
                            '$ref' => '#/definitions/empty',
                        ],
                        (object) [
                            '$ref' => '#/definitions/linkage',
                        ],
                    ],
                ],
                'relationshipToMany' => (object) [
                    'type' => 'array',
                    'items' => (object) [
                        '$ref' => '#/definitions/linkage',
                    ],
                    'uniqueItems' => true,
                ],
                'empty' => (object) [
                    'type' => 'null',
                ],
                'linkage' => (object) [
                    'type' => 'object',
                    'required' => ['type', 'id'],
                    'properties' => (object) [
                        'type' => (object) [
                            'type' => 'string',
                        ],
                        'id' => (object) [
                            'type' => 'string',
                        ],
                    ],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }

    /**
     * Returns the JSON schema for the JSON API PATCH request document
     * @return \stdClass
     */
    private function getPatchSchema(): \stdClass
    {
        return (object) [
            'type' => 'object',
            'properties' => (object) [
                'data' => (object) [
                    '$ref' => '#/definitions/resource',
                ],
            ],
            'definitions' => (object) [
                'resource' => (object) [
                    'type' => 'object',
                    'required' => ['type','id'],
                    'properties' => (object) [
                        'type' => (object) [
                            'type' => 'string',
                        ],
                        'id' => (object) [
                            'type' => 'string',
                        ],
                        'attributes' => (object) [
                            '$ref' => '#/definitions/attributes',
                        ],
                        'relationships' => (object) [
                            '$ref' => '#/definitions/relationships',
                        ],
                    ],
                    'additionalProperties' => false,
                ],
                'attributes' => (object) [
                    'type' => 'object',
                    'patternProperties' => (object) [
                        '^(?!relationships$|links$|id$|type$)\\w[-\\w_]*$' => (object) [
                            'not' => (object) [
                                'type' => ['object', 'array'],
                            ],
                        ],
                    ],
                    'additionalProperties' => false,
                ],
                'relationships' => (object) [
                    'type' => 'object',
                    'patternProperties' => (object) [
                        '^(?!id$|type$)\\w[-\\w_]*$' => (object) [
                            'type' => 'object',
                            'properties' => (object) [
                                'data' => (object) [
                                    'oneOf' => [
                                        (object) [
                                            '$ref' => '#/definitions/relationshipToOne',
                                        ],
                                        (object) [
                                            '$ref' => '#/definitions/relationshipToMany',
                                        ],
                                    ],
                                ],
                            ],
                            'required' => ['data'],
                            'additionalProperties' => false,
                        ],
                    ],
                    'additionalProperties' => false,
                ],
                'relationshipToOne' => (object) [
                    'anyOf' => [
                        (object) [
                            '$ref' => '#/definitions/empty',
                        ],
                        (object) [
                            '$ref' => '#/definitions/linkage',
                        ],
                    ],
                ],
                'relationshipToMany' => (object) [
                    'type' => 'array',
                    'items' => (object) [
                        '$ref' => '#/definitions/linkage',
                    ],
                    'uniqueItems' => true,
                ],
                'empty' => (object) [
                    'type' => 'null',
                ],
                'linkage' => (object) [
                    'type' => 'object',
                    'required' => ['type', 'id'],
                    'properties' => (object) [
                        'type' => (object) [
                            'type' => 'string',
                        ],
                        'id' => (object) [
                            'type' => 'string',
                        ],
                    ],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }

    /**
     * Returns the JSON schema for the JSON API Relationship request document
     * @return \stdClass
     */
    private function getToOneRelationshipSchema(): \stdClass
    {
        return (object) [
            'type' => 'object',
            'properties' => (object) [
                'data' => (object) [
                    'anyOf' => [
                        (object) [
                            '$ref' => '#/definitions/empty',
                        ],
                        (object) [
                            '$ref' => '#/definitions/linkage',
                        ],
                    ],
                ],
            ],
            'definitions' => (object) [
                'empty' => (object) [
                    'type' => 'null',
                ],
                'linkage' => (object) [
                    'type' => 'object',
                    'required' => ['type', 'id'],
                    'properties' => (object) [
                        'type' => (object) [
                            'type' => 'string',
                        ],
                        'id' => (object) [
                            'type' => 'string',
                        ],
                    ],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }

    /**
     * Returns the JSON schema for the JSON API Relationship request document
     * @return \stdClass
     */
    private function getToManyRelationshipSchema(): \stdClass
    {
        return (object) [
            'type' => 'object',
            'properties' => (object) [
                'data' => (object) [
                    'type' => 'array',
                    'items' => (object) [
                        '$ref' => '#/definitions/linkage',
                    ],
                    'uniqueItems' => true,
                ],
            ],
            'definitions' => (object) [
                'linkage' => (object) [
                    'type' => 'object',
                    'required' => ['type', 'id'],
                    'properties' => (object) [
                        'type' => (object) [
                            'type' => 'string',
                        ],
                        'id' => (object) [
                            'type' => 'string',
                        ],
                    ],
                    'additionalProperties' => false,
                ],
            ],
        ];
    }
}