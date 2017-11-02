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

namespace Vallarj\JsonApi\Schema;


use Zend\Validator;

class DateAttribute implements AttributeInterface
{
    /** @var string Specifies the key of the attribute */
    private $key = "";

    /** @var int Access type. Defaults to read and write. */
    private $accessType = self::ACCESS_READ | self::ACCESS_WRITE;

    /** @var bool Specifies if attribute is required. */
    private $isRequired = false;

    /** @var Validator\Date Date validator */
    private $validator;

    /**
     * @inheritdoc
     */
    public function setOptions(array $options): void
    {
        if(isset($options['key'])) {
            $this->key = $options['key'];
        }

        if(isset($options['required'])) {
            $this->setRequired($options['required']);
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
        $dateTimeValue = $parentObject->{'get' . ucfirst($this->key)}();
        if($dateTimeValue instanceof \DateTime) {
            return $dateTimeValue->format(\DateTime::ATOM);
        }

        return null;
    }

    /**
     * @inheritdoc
     */
    public function setValue($parentObject, $value): void
    {
        if(!is_null($value)) {
            $value = \DateTime::createFromFormat(DATE_ATOM, $value);
        }
        $parentObject->{'set' . ucfirst($this->key)}($value);
    }

    /**
     * @inheritdoc
     */
    public function getAccessType(): int
    {
        return $this->accessType;
    }

    /**
     * Sets the access type of this attribute
     * @param int $accessFlag
     * @return $this
     */
    public function setAccessType(int $accessFlag)
    {
        $this->accessType = $accessFlag;
        return $this;
    }

    /**
     * Gets the validator of this attribute
     * @return Validator\Date
     */
    private function getValidator(): Validator\Date
    {
        if(!$this->validator) {
            $this->validator = new Validator\Date(["format" => \DateTime::ATOM]);
            $this->validator->setMessage("Date must follow ISO-8601 format", Validator\Date::INVALID_DATE);
            $this->validator->setMessage("Date must follow ISO-8601 format", Validator\Date::FALSEFORMAT);
        }

        return $this->validator;
    }

    /**
     * @inheritdoc
     */
    public function filterValue($value)
    {
        // Do not perform any pre-processing
        return $value;
    }

    /**
     * @inheritdoc
     */
    public function isValid($value): bool
    {
        // Workaround for null $value
        if(is_null($value)) {
            $value = "";
        }
        return $this->getValidator()->isValid($value);
    }

    /**
     * @inheritdoc
     */
    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    /**
     * Sets the attribute required flag
     * @param bool $required
     */
    public function setRequired(bool $required)
    {
        $this->isRequired = $required;
    }

    /**
     * @inheritdoc
     */
    public function getErrorMessages(): array
    {
        return $this->getValidator()->getMessages();
    }
}