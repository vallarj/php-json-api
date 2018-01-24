<?php
/**
 * Created by PhpStorm.
 * User: justin
 * Date: 1/24/18
 * Time: 4:03 PM
 */

namespace Vallarj\JsonApi\Schema;


interface MetaInterface
{
    /**
     * Set options of this specification
     *
     * @param array $options    Array that contains the options for this specification
     */
    public function setOptions(array $options): void;

    /**
     * Returns the meta item key
     *
     * @return string
     */
    public function getKey(): string;

    /**
     * Returns the value of the meta item
     *
     * @param $parentObject
     * @return mixed
     */
    public function getValue($parentObject);
}