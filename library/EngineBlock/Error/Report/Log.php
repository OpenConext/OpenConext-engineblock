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
 
class EngineBlock_Error_Report_Log implements EngineBlock_Error_Report_Interface
{
    public function __construct($config)
    {
    }

    public function report(Exception $exception)
    {
        $application = EngineBlock_ApplicationSingleton::getInstance();
        $log = $application->getLog();
        if ($log) {
            $log->err($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
        }
        else {
            // Catch-22, we don't have a log, so we can't report that we don't have a log as an error
            // We assume that if this is the case something serious is very wrong and we panic
            die("Panic! Unable to log errors, please contact your administrator and ask him to check the log file." . PHP_EOL);
        }
    }
}
