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

namespace Vallarj\JsonApi\Error\Source;


class Pointer implements SourceInterface
{
    /** @var string Reference to the error */
    private $reference;

    /**
     * Pointer constructor.
     * @param string $reference
     */
    function __construct(string $reference)
    {
        $this->reference = $reference;
    }

    /**
     * @inheritdoc
     */
    public function getType(): string
    {
        return "pointer";
    }

    /**
     * @inheritdoc
     */
    public function getReference(): string
    {
        return $this->reference;
    }
}