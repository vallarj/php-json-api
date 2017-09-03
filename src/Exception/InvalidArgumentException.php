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

namespace Vallarj\JsonApi\Exception;


class InvalidArgumentException extends \InvalidArgumentException
{
    /**
     * Thrown when adding an invalid type of argument to the addAttribute method
     * of ResourceSchema class
     * @return InvalidArgumentException
     */
    public static function fromResourceSchemaAddAttribute()
    {
        return new self("Argument must be an instance of SchemaAttribute or an array compatible " .
            "to schema attribute builder specifications");
    }

    /**
     * Thrown when adding an invalid type of argument to the addRelationship method
     * of ResourceSchema class
     * @return InvalidArgumentException
     */
    public static function fromResourceSchemaAddRelationship()
    {
        return new self("Argument must be an instance of SchemaRelationship or an array " .
            "compatible to schema relationship builder specifications");
    }
}