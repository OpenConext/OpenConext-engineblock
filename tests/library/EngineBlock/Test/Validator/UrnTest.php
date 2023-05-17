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

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class EngineBlock_Test_Validator_UrnTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    /**
     * @var EngineBlock_Validator_Urn
     */
    private $validator;

    public function setUp(): void
    {
        $this->validator = new EngineBlock_Validator_Urn();
    }

    /**
     * @dataProvider validUrnProvider
     */
    public function testUrnValidates($urn)
    {
        $this->assertTrue($this->validator->validate($urn));
    }

    /**
     * @dataProvider invalidUrnProvider
     */
    public function testUrnValidationFails($invalidUrn)
    {
        $this->assertFalse($this->validator->validate($invalidUrn));
    }

    /**
     * @return array
     */
    public function validUrnProvider()
    {
        $filename = TEST_RESOURCES_DIR .'/validator/urn/valid-urns.php';

        // Uncomment to regenerate test data from metadata config
        //$this->createTestDataFromMetadata(ENGINEBLOCK_FOLDER_APPLICATION . 'configs/attributes-SURFconext.json', $filename);

        return require $filename;
    }

    /**
     * @return array
     */
    public function invalidUrnProvider()
    {
        return require TEST_RESOURCES_DIR . '/validator/urn/invalid-urns.php';
    }

    private function isUrn($string)
    {
        return substr($string, 0, 3) === 'urn';
    }

    /**
     * Creates test data in PHPUnit data provider format, was used to generate the test data and can be used to update it
     *
     * @param string $metadataFile
     * @param string $testDataFile
     */
    private function createTestDataFromMetadata($metadataFile, $testDataFile)
    {
        $metadata = json_decode(file_get_contents($metadataFile), true);

        $urns = array();
        foreach($metadata as $key => $value) {
            if ($this->isUrn($key)) {
                $urns[$key] = array($key);
            }
            if (is_string($value) && $this->isUrn($value)) {
                $urns[$value] = array($value);
            }
        }

        // Remove known invalid metadata config
        unset($urns['urn:nl.surfconext.licenseInfo']);

        ksort($urns);
        $export = var_export($urns, true);
        $export = '<?php' . PHP_EOL . 'return ' . $export . ';';

        file_put_contents($testDataFile, $export);
    }
}
