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

namespace Vallarj\JsonApi\Schema;


class Identifier implements IdentifierInterface
{
    private $key = "id";

    /**
     * @inheritdoc
     */
    public function setOptions(array $options)
    {
        if(isset($options['key'])) {
            $this->setIdentifierKey($options['key']);
        }
    }

    /**
     * @inheritdoc
     */
    final public function getIdentifierKey(): string
    {
        return $this->key;
    }

    /**
     * Sets the identifier key
     * @param string $key
     */
    final public function setIdentifierKey(string $key)
    {
        $this->key = $key;
    }

    /**
     * @inheritdoc
     */
    final public function getResourceId($object)
    {
        return $object->{'get' . ucfirst($this->getIdentifierKey())}();
    }

    /**
     * @inheritdoc
     */
    final public function setResourceId($object, $id): void
    {
        $object->{'set' . ucfirst($this->getIdentifierKey())}($id);
    }
}