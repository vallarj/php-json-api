<?php
/**
 *  Copyright 2017 Justin Dane D. Vallar
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

namespace Vallarj\JsonApi\Error;


use Vallarj\JsonApi\Error\Source\SourceInterface;

class Error
{
    /** @var mixed A unique identifier for this particular occurrence of the problem. */
    private $id;

    /** @var mixed A links containing details about this particular occurrence of the problem */
    private $links;

    /** @var string The HTTP status code applicable to this problem */
    private $status;

    /** @var string An application-specific error code, expressed as a string value */
    private $code;

    /**
     * @var string  A short, human-readable summary of the problem that should not change from
     *              occurrence to occurrence of the problem, except for purposes of localization
     */
    private $title;

    /**
     * @var string  A human-readable explanation specific to this occurrence of the problem.
     *              Like title, this field's value can be localized
     */
    private $detail;

    /** @var SourceInterface An object containing references to the source of the error */
    private $source;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     * @return Error
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * @param mixed $links
     * @return Error
     */
    public function setLinks($links)
    {
        $this->links = $links;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return Error
     */
    public function setStatus(string $status): Error
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getCode(): ?string
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Error
     */
    public function setCode(string $code): Error
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return Error
     */
    public function setTitle(string $title): Error
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getDetail(): ?string
    {
        return $this->detail;
    }

    /**
     * @param string $detail
     * @return Error
     */
    public function setDetail(string $detail): Error
    {
        $this->detail = $detail;
        return $this;
    }

    /**
     * @return SourceInterface
     */
    public function getSource(): ?SourceInterface
    {
        return $this->source;
    }

    /**
     * @param SourceInterface $source
     * @return Error
     */
    public function setSource(SourceInterface $source): Error
    {
        $this->source = $source;
        return $this;
    }
}