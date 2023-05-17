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

namespace OpenConext\EngineBlock\Metadata\X509;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class X509KeyTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    private $filePath;

    public function setUp(): void
    {
        $this->filePath = 'file://' . __DIR__ . '/test.pem.crt';
        $this->filePath2 = 'file://' . __DIR__ . '/test2.pem.crt';
    }

    public function testToPem()
    {
        $key = new X509Certificate(openssl_x509_read(file_get_contents($this->filePath)));
        $this->assertEquals(file_get_contents($this->filePath), $key->toPem());
    }

    public function testToCertData()
    {
        $key = new X509Certificate(openssl_x509_read(file_get_contents($this->filePath)));
        $this->assertEquals(
            "MIIDXzCCAkegAwIBAgIJAM4CwNsdIhJ3MA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMMBkVuZ2luZTERMA8GA1UECwwIU2VydmljZXMxEzARBgNVBAoMCk9wZW5Db25leHQxCzAJBgNVBAYTAk5MMB4XDTE0MDUxMjEzMjIxNloXDTI0MDUxMTEzMjIxNlowRjEPMA0GA1UEAwwGRW5naW5lMREwDwYDVQQLDAhTZXJ2aWNlczETMBEGA1UECgwKT3BlbkNvbmV4dDELMAkGA1UEBhMCTkwwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQC2aQ9OYsAASWR/aN5NB2mQqFsBc13uN0nSbjkk1Um8VouGo7OmSM0eiur5my8UvPYth1DXQM6u2wiFq19RtfVZWJOmrzAfVHc9VRj9Xj4T+MVpR4bDWctvxVT1OPm9L23KQKvaqDmUo7uSPsBD36EIH7dFOBydDtSXfZTW0ien+lZr6C4nPuxzDbHJ+Jlo2brieimUBQNetX/ettnAglJ9536sJDkhsa120mkYPhVnvepbOtxPyU5ZDUpDNmMQR2/SORCBJcfvLSVZ4It4O67l6/EJnkFRLerIqOpk/W8jY3USQaLM2WM7sWBGxEFKDVcTFgrOH50Z94K2M/KweY2bAgMBAAGjUDBOMB0GA1UdDgQWBBSewI9OzfzbIxnl6XMkaQkYY1hHPjAfBgNVHSMEGDAWgBSewI9OzfzbIxnl6XMkaQkYY1hHPjAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBBQUAA4IBAQAFfYPZbsYPHz4ypV/aO59do7CtHPnAMWr0NcQt4h9IW8gjihaNHt12V30QtHrVaXejXybB/LaGbPPyA64+l/SeC7ksrRxlitCwFqnws6ISXJaYU0iEFHGUD/cAj1iGloIsOm5IOdb3sdG/SsBv49G8es2wG0rDd0/s2fBVvXd4qUoXzKJAjYk1MFQxnGHomlt67SBrr2QLh+m2VHg+mkdi6yrdm9B9ylF8V55Vl82pPZXxphIRgqdos5YWeALS7dr5dSw9s5smFBxyy8IfCQMxagfNC59w22w2ULC/J7au/oP8ylusuxncxizdR/+5UazzAlOWtkjzaABzzBWM4hEK",
            $key->toCertData()
        );
    }

    public function testFromCert()
    {
        $key = new X509Certificate(openssl_x509_read(file_get_contents($this->filePath2)));
        $this->assertEquals(
           "MIIDXzCCAkegAwIBAgIJAM4CwNsdIhJ3MA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMMBkVuZ2luZTERMA8GA1UECwwIU2VydmljZXMxEzARBgNVBAoMCk9wZW5Db25leHQxCzAJBgNVBAYTAk5MMB4XDTE0MDUxMjEzMjIxNloXDTI0MDUxMTEzMjIxNlowRjEPMA0GA1UEAwwGRW5naW5lMREwDwYDVQQLDAhTZXJ2aWNlczETMBEGA1UECgwKT3BlbkNvbmV4dDELMAkGA1UEBhMCTkwwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQC2aQ9OYsAASWR/aN5NB2mQqFsBc13uN0nSbjkk1Um8VouGo7OmSM0eiur5my8UvPYth1DXQM6u2wiFq19RtfVZWJOmrzAfVHc9VRj9Xj4T+MVpR4bDWctvxVT1OPm9L23KQKvaqDmUo7uSPsBD36EIH7dFOBydDtSXfZTW0ien+lZr6C4nPuxzDbHJ+Jlo2brieimUBQNetX/ettnAglJ9536sJDkhsa120mkYPhVnvepbOtxPyU5ZDUpDNmMQR2/SORCBJcfvLSVZ4It4O67l6/EJnkFRLerIqOpk/W8jY3USQaLM2WM7sWBGxEFKDVcTFgrOH50Z94K2M/KweY2bAgMBAAGjUDBOMB0GA1UdDgQWBBSewI9OzfzbIxnl6XMkaQkYY1hHPjAfBgNVHSMEGDAWgBSewI9OzfzbIxnl6XMkaQkYY1hHPjAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBBQUAA4IBAQAFfYPZbsYPHz4ypV/aO59do7CtHPnAMWr0NcQt4h9IW8gjihaNHt12V30QtHrVaXejXybB/LaGbPPyA64+l/SeC7ksrRxlitCwFqnws6ISXJaYU0iEFHGUD/cAj1iGloIsOm5IOdb3sdG/SsBv49G8es2wG0rDd0/s2fBVvXd4qUoXzKJAjYk1MFQxnGHomlt67SBrr2QLh+m2VHg+mkdi6yrdm9B9ylF8V55Vl82pPZXxphIRgqdos5YWeALS7dr5dSw9s5smFBxyy8IfCQMxagfNC59w22w2ULC/J7au/oP8ylusuxncxizdR/+5UazzAlOWtkjzaABzzBWM4hEK",
            $key->toCertData()
        );
    }
}
