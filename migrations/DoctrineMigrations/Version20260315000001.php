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
 * Change to the consent schema
 * 1. Added the `attribute_stable` column, string(80), nullable
 * 2. Changed the `attribute` column, has been made nullable
 */
final class Version20260315000001 extends AbstractEngineBlockMigration
{
    public function getDescription(): string
    {
        return 'Add attribute_stable column to consent table and make attribute nullable';
    }

    public function preUp(Schema $schema): void
    {
        parent::preUp($schema);
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE consent ADD attribute_stable VARCHAR(80) DEFAULT NULL, CHANGE attribute attribute VARCHAR(80) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE consent SET attribute = attribute_stable WHERE attribute IS NULL AND attribute_stable IS NOT NULL');
        $this->addSql('ALTER TABLE consent CHANGE attribute attribute VARCHAR(80) NOT NULL');
        $this->addSql('ALTER TABLE consent DROP attribute_stable');
    }
}
