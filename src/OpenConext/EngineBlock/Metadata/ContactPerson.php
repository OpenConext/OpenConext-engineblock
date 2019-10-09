<?php

/**
 * Copyright 2010 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace OpenConext\EngineBlock\Metadata;

/**
 * Class ContactPerson
 * @package OpenConext\EngineBlock\Metadata
 */
class ContactPerson
{
    public $contactType;
    public $emailAddress = '';
    public $telephoneNumber = '';
    public $givenName = '';
    public $surName = '';

    /**
     * @param $contactType
     */
    public function __construct($contactType)
    {
        $this->contactType = $contactType;
    }

    /**
     * A convenience static constructor for the contact person.
     * @param string $type
     * @param string $givenName
     * @param string $surname
     * @param string $emailAddress
     * @param string $telephoneNumber
     * @return ContactPerson
     */
    public static function from(
        string $type,
        string $givenName,
        string $surname,
        string $emailAddress,
        string $telephoneNumber = ''
    ): ContactPerson {
        $contact = new self($type);
        $contact->givenName = $givenName;
        $contact->surName = $surname;
        $contact->emailAddress = $emailAddress;
        $contact->telephoneNumber = $telephoneNumber;
        return $contact;
    }
}
