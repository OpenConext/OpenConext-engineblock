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

use Doctrine\DBAL\Platforms\MariaDBPlatform;
use Doctrine\DBAL\Platforms\MySQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Base class for all EngineBlock Doctrine migrations.
 *
 * All migrations in this project target MariaDB exclusively. The generated DDL SQL is platform-specific
 * and is not guaranteed to be compatible with MySQL or any other database engine.
 *
 */
abstract class AbstractEngineBlockMigration extends AbstractMigration
{
    public function preUp(Schema $schema): void
    {
        $this->checkPlatform();
    }

    public function preDown(Schema $schema): void
    {
        $this->checkPlatform();
    }

    private function checkPlatform(): void
    {
        if ($this->platform instanceof MariaDBPlatform) {
            return;
        }

        if ($this->platform instanceof MySQLPlatform) {
            $this->warnIf(
                true,
                sprintf(
                    'This migration is built for MariaDB. The current database platform is MySQL ("%s"). '
                    . 'EngineBlock migrations may contain MariaDB-specific DDL that may fail. '
                    . 'Check manually to ensure the migrations run as expected.',
                    get_class($this->platform),
                ),
            );
            return;
        }

        $this->abortIf(
            true,
            sprintf(
                'This migration is built for MariaDB only. The current database platform "%s" is not supported.',
                get_class($this->platform),
            ),
        );
    }
}
