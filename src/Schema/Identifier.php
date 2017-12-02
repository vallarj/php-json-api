<?php
/**
 * Created by PhpStorm.
 * User: justin
 * Date: 12/2/17
 * Time: 11:19 AM
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