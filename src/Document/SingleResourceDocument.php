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


class SingleResourceDocument extends AbstractDocument
{
    private $boundObject;
    private $resourceAttributes;
    private $resourceRelationships;

    /**
     * SingleResourceDocument constructor.
     */
    function __construct()
    {
        parent::__construct();
        $this->resourceAttributes = [];
        $this->resourceRelationships = [];
    }

    /**
     * Binds an object to the document
     * @param $object
     */
    public function bind($object)
    {
        $this->boundObject = $object;
    }
}