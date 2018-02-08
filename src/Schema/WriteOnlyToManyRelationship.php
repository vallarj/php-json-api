<?php
/**
 * Created by PhpStorm.
 * User: justin
 * Date: 2/8/18
 * Time: 2:24 PM
 */

namespace Vallarj\JsonApi\Schema;


class WriteOnlyToManyRelationship extends ToManyRelationship
{
    /**
     * Overrides the isReadable function to always return false
     * @inheritdoc
     */
    public function isReadable(): bool
    {
        return false;
    }

    /**
     * Overrides the isWritable function to always return true
     * @inheritdoc
     */
    public function isWritable(): bool
    {
        return true;
    }
}