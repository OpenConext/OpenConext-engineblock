<?php
/**
 * SURFconext EngineBlock
 *
 * LICENSE
 *
 * Copyright 2011 SURFnet bv, The Netherlands
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and limitations under the License.
 *
 * @category  SURFconext EngineBlock
 * @package
 * @copyright Copyright © 2010-2011 SURFnet SURFnet bv, The Netherlands (http://www.surfnet.nl)
 * @license   http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 */

class OpenSocial_Model_Person
    implements OpenSocial_Model_Interface
{
    public $id;
    public $name;
    public $nickname;
    public $displayName;
    public $updated;

    public $anniversary;
    public $birthday;
    public $connected;
    public $gender;
    public $note;
    public $preferredUsername;
    public $published;
    public $utcOffset;

    // plurals
    public $emails          = array();
    public $urls            = array();
    public $phoneNumbers    = array();
    public $ims             = array();
    public $photos          = array();
    public $tags            = array();
    public $relationships   = array();
    public $addresses       = array();
    public $organizations   = array();
    public $accounts        = array();
    public $appdata         = array();
}