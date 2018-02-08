<?php
/**
 * Created by PhpStorm.
 * User: justin
 * Date: 2/8/18
 * Time: 2:00 PM
 */

namespace Vallarj\JsonApi\Schema;


class ReadOnlyDateAttribute extends DateAttribute
{
    /**
     * Overrides the isReadable function to always return true
     * @inheritdoc
     */
    public function isReadable(): bool
    {
        return true;
    }

    /**
     * Overrides the isWritable function to always return false
     * @inheritdoc
     */
    public function isWritable(): bool
    {
        return false;
    }
}