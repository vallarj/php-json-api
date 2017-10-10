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


class Attribute implements AttributeInterface
{
    /** @var string Specifies the key of the attribute */
    private $key = "";

    /** @var int Access type. Defaults to read and write. */
    private $accessType = self::ACCESS_READ | self::ACCESS_WRITE;

    /**
     * @inheritdoc
     */
    public function setOptions(array $options): void
    {
        if(isset($options['key'])) {
            $this->key = $options['key'];
        }

        if(isset($options['accessType'])) {
            $this->setAccessType($options['accessType']);
        }
    }

    /**
     * @inheritdoc
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @inheritdoc
     */
    public function getValue($parentObject)
    {
        return $parentObject->{'get' . ucfirst($this->key)}();
    }

    /**
     * @inheritdoc
     */
    public function setValue($parentObject, $value): void
    {
        $parentObject->{'set' . ucfirst($this->key)}($value);
    }

    /**
     * @inheritdoc
     */
    public function getAccessType(): int
    {
        return $this->accessType;
    }

    /**
     * Sets the access type of this attribute
     * @param int $accessFlag
     * @return $this
     */
    public function setAccessType(int $accessFlag)
    {
        $this->accessType = $accessFlag;
        return $this;
    }
}