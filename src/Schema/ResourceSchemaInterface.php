<?php
/**
 * Created by PhpStorm.
 * User: justin
 * Date: 12/2/17
 * Time: 1:26 PM
 */

namespace Vallarj\JsonApi\Schema;


interface ResourceSchemaInterface
{
    /**
     * Returns the resource type used by this schema
     *
     * @return string
     */
    public function getResourceType(): ?string;

    /**
     * Returns the FQCN of the object to map the JSON API resource
     *
     * @return string
     */
    public function getMappingClass(): ?string;

    /**
     * Returns the identifier specifications of the schema
     *
     * @return IdentifierInterface
     */
    public function getIdentifier(): IdentifierInterface;

    /**
     * Returns the attributes of this schema
     *
     * @return AttributeInterface[]
     */
    public function getAttributes(): array;

    /**
     * Returns the relationships of this schema
     *
     * @return array
     */
    public function getRelationships(): array;

    /**
     * Returns the meta items of this schema
     *
     * @return MetaInterface[]
     */
    public function getMeta(): array;
}