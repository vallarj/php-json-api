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

namespace Vallarj\JsonApi\Exception;


class InvalidArgumentException extends \InvalidArgumentException
{
    public static function fromAbstractDocumentAddSchema()
    {
        return new self("Argument must be an instance of ResourceSchema or an array compatible " .
            "with ResourceSchema builder specifications");
    }

    /**
     * Thrown when adding an invalid type of argument to the addAttribute method
     * of ResourceSchema class
     * @return InvalidArgumentException
     */
    public static function fromResourceSchemaAddSchemaAttribute()
    {
        return new self("Argument must be an instance of Attribute or an array compatible " .
            "with schema attribute builder specifications");
    }

    /**
     * Thrown when adding an invalid type of argument to the addRelationship method
     * of ResourceSchema class
     * @return InvalidArgumentException
     */
    public static function fromResourceSchemaAddRelationship()
    {
        return new self("Argument must be an instance of NestedRelationship or an array " .
            "compatible with schema relationship builder specifications");
    }

    public static function fromNestedSchemaRelationshipAddExpectedResource()
    {
        return new self("Argument must be an instance of ResourceIdentifierSchema or an array compatible " .
            "with ResourceIdentifierSchema builder specifications");
    }

    public static function fromSingleResourceDocumentBind()
    {
        return new self("Argument must be an object.");
    }

    public static function fromResourceCollectionDocumentAddResource()
    {
        return new self("Argument must be an object.");
    }

    public static function fromAbstractSchemaRelationshipSetCardinality()
    {
        return new self("Argument must be one of:" .
            "AbstractRelationship::TO_ONE, AbstractRelationship::TO_MANY");
    }

    public static function fromAbstractSchemaRelationshipSetRelationship()
    {
        return new self("Array must follow the required format");
    }
}