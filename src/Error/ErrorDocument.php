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

namespace Vallarj\JsonApi\Error;


class ErrorDocument implements \JsonSerializable
{
    /** @var string Equivalent HTTP Status Code */
    private $statusCode;

    /** @var Error[] Collection of errors  */
    private $errors;

    function __construct($statusCode = "400")
    {
        $this->statusCode = $statusCode;
        $this->errors = [];
    }

    /**
     * Add an error
     * @param Error $error
     */
    public function addError(Error $error): void
    {
        $this->errors[] = $error;
    }

    /**
     * Get the errors
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Gets the equivalent HTTP Status Code
     * @return string
     */
    public function getStatusCode(): string
    {
        return $this->statusCode;
    }

    /**
     * Sets the equivalent HTTP Status Code
     * @param string $statusCode
     */
    public function setStatusCode(string $statusCode)
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        $errors = [];
        $members = [
            'id',
            'status',
            'code',
            'title',
            'detail',
        ];

        foreach($this->errors as $error) {
            $errorItem = [];
            foreach($members as $member) {
                if(!is_null($value = $error->{'get'. ucfirst($member)}())) {
                    $errorItem[$member] = $value;
                }

                // Special case for source
                if(!is_null($source = ($error->getSource()))) {
                    $errorItem['source'] = [
                        $source->getType() => $source->getReference()
                    ];
                }
            }

            $errors[] = $errorItem;
        }

        return [
            "errors" => $errors
        ];
    }
}