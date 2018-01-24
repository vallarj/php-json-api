<?php
/**
 * Created by PhpStorm.
 * User: justin
 * Date: 1/24/18
 * Time: 4:06 PM
 */

namespace Vallarj\JsonApi\Schema;


class Meta implements MetaInterface
{
    /** @var string Specifies the key of the meta item */
    private $key = "";

    /**
     * @inheritdoc
     */
    public function setOptions(array $options): void
    {
        if(isset($options['key'])) {
            $this->key = $options['key'];
        }
    }

    /**
     * @inheritdoc
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @inheritdoc
     */
    public function getValue($parentObject)
    {
        return $parentObject->{'get' . ucfirst($this->key)}();
    }
}