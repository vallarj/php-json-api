<?php
/**
 * Created by PhpStorm.
 * User: justin
 * Date: 12/2/17
 * Time: 11:17 AM
 */

namespace Vallarj\JsonApi\Schema;


interface IdentifierInterface
{
    /**
     * Allows implementor to receive options array
     *
     * @param array $options
     * @return mixed
     */
    public function setOptions(array $options);

    /**
     * Must return the identifier key name of the bound object
     *
     * @return string
     */
    public function getIdentifierKey(): string;

    /**
     * Extracts the resource ID based on identifier key
     *
     * @param $object
     * @return mixed
     */
    public function getResourceId($object);

    /**
     * Sets the resource ID based on identifier key
     *
     * @param $object
     * @param mixed $id
     */
    public function setResourceId($object, $id): void;
}