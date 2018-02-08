<?php
/**
 * Created by PhpStorm.
 * User: justin
 * Date: 2/8/18
 * Time: 1:58 PM
 */

namespace Vallarj\JsonApi\Schema;


class ReadOnlyAttribute extends Attribute
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
     * @return bool
     */
    public function isWritable(): bool
    {
        return false;
    }
}