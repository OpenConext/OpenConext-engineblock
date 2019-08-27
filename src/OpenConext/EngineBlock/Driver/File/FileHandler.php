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

namespace OpenConext\EngineBlock\Driver\File;

interface FileHandler
{
    /**
     * @param mixed  $data     the data to write
     * @param string $filePath the path to the file to write to
     * @return void
     */
    public function writeTo($data, $filePath);

    /**
     * @param string $filePath the path to the file to read the contents of
     * @return mixed
     */
    public function readFrom($filePath);

    /**
     * @param string $filePath the path to the file to remove
     * @return void
     */
    public function remove($filePath);
}
