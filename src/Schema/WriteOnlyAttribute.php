<?php
/**
 * Created by PhpStorm.
 * User: justin
 * Date: 2/8/18
 * Time: 1:59 PM
 */

namespace Vallarj\JsonApi\Schema;


class WriteOnlyAttribute extends Attribute
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
     * @return bool
     */
    public function isWritable(): bool
    {
        return true;
    }
}