<?php

/**
 * Copyright 2026 SURFnet B.V.
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

declare(strict_types=1);

namespace OpenConext\EngineBlock\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;

/**
 * Corrects the column comment on saml_persistent_id.persistent_id.
 *
 * The original comment read "SHA1 of service_provider_uuid + user_uuid", which was
 * inaccurate in two ways: the operand order was wrong, and the COIN: salt was omitted.
 * The actual value stored is sha1('COIN:' + user_uuid + service_provider_uuid), as
 * defined in EngineBlock_Saml2_NameIdResolver::PERSISTENT_NAMEID_SALT.
 *
 * NOTE: This migration is NOT mandatory. It only updates a database-level column comment
 * and has no effect on data integrity or application behaviour. It is safe to skip on
 * existing installations where updating the comment is not considered necessary.
 */
final class Version20260331000000 extends AbstractEngineBlockMigration
{
    public function getDescription(): string
    {
        return 'Corrects the column comment on saml_persistent_id.persistent_id to accurately reflect the SHA1 formula.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(
            "ALTER TABLE `saml_persistent_id` MODIFY COLUMN `persistent_id` CHAR(40) NOT NULL COMMENT 'SHA1 of COIN: + user_uuid + service_provider_uuid'"
        );
    }

    public function down(Schema $schema): void
    {
        $this->addSql(
            "ALTER TABLE `saml_persistent_id` MODIFY COLUMN `persistent_id` CHAR(40) NOT NULL COMMENT 'SHA1 of service_provider_uuid + user_uuid'"
        );
    }
}
