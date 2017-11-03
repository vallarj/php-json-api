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

namespace Vallarj\JsonApi\Schema;


class ValidationResult implements ValidationResultInterface
{
    private $isValid;
    private $messages;

    function __construct(bool $isValid)
    {
        $this->isValid = $isValid;
        $this->messages = [];
    }

    /**
     * @inheritdoc
     */
    public function isValid(): bool
    {
        return $this->isValid();
    }

    /**
     * @inheritdoc
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Add a message
     * @param string $message
     */
    public function addMessage(string $message)
    {
        $this->messages[] = $message;
    }
}