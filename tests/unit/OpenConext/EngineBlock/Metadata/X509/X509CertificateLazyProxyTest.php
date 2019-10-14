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

use Exception;
use PHPUnit\Framework\TestCase;

/**
 * Class PrivateKeyTest
 * @package OpenConext\EngineBlock\Metadata\X509
 */
class X509CertificateLazyProxyTest extends TestCase
{
    public function testDeferralOfException()
    {
        $factory = new X509CertificateFactory();

        $e = null;
        try {
            $proxy = new X509CertificateLazyProxy($factory, 'abcd');
        } catch (Exception $e) {
        }
        $this->assertNull($e, 'Lazy proxy does not blow up when instantiating with wrong input');

        try {
            /** @var X509Certificate $proxy */
            @$proxy->toPem();
        } catch (Exception $e) {
        }
        $this->assertNotNull($e, 'Lazy proxy blows up when instantiated with wrong input');
    }

    public function testHappyPath()
    {
        $factory = new X509CertificateFactory();
        $certData = "MIIDXzCCAkegAwIBAgIJAM4CwNsdIhJ3MA0GCSqGSIb3DQEBBQUAMEYxDzANBgNVBAMMBkVuZ2luZTERMA8GA1UECwwIU2VydmljZXMxEzARBgNVBAoMCk9wZW5Db25leHQxCzAJBgNVBAYTAk5MMB4XDTE0MDUxMjEzMjIxNloXDTI0MDUxMTEzMjIxNlowRjEPMA0GA1UEAwwGRW5naW5lMREwDwYDVQQLDAhTZXJ2aWNlczETMBEGA1UECgwKT3BlbkNvbmV4dDELMAkGA1UEBhMCTkwwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQC2aQ9OYsAASWR/aN5NB2mQqFsBc13uN0nSbjkk1Um8VouGo7OmSM0eiur5my8UvPYth1DXQM6u2wiFq19RtfVZWJOmrzAfVHc9VRj9Xj4T+MVpR4bDWctvxVT1OPm9L23KQKvaqDmUo7uSPsBD36EIH7dFOBydDtSXfZTW0ien+lZr6C4nPuxzDbHJ+Jlo2brieimUBQNetX/ettnAglJ9536sJDkhsa120mkYPhVnvepbOtxPyU5ZDUpDNmMQR2/SORCBJcfvLSVZ4It4O67l6/EJnkFRLerIqOpk/W8jY3USQaLM2WM7sWBGxEFKDVcTFgrOH50Z94K2M/KweY2bAgMBAAGjUDBOMB0GA1UdDgQWBBSewI9OzfzbIxnl6XMkaQkYY1hHPjAfBgNVHSMEGDAWgBSewI9OzfzbIxnl6XMkaQkYY1hHPjAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBBQUAA4IBAQAFfYPZbsYPHz4ypV/aO59do7CtHPnAMWr0NcQt4h9IW8gjihaNHt12V30QtHrVaXejXybB/LaGbPPyA64+l/SeC7ksrRxlitCwFqnws6ISXJaYU0iEFHGUD/cAj1iGloIsOm5IOdb3sdG/SsBv49G8es2wG0rDd0/s2fBVvXd4qUoXzKJAjYk1MFQxnGHomlt67SBrr2QLh+m2VHg+mkdi6yrdm9B9ylF8V55Vl82pPZXxphIRgqdos5YWeALS7dr5dSw9s5smFBxyy8IfCQMxagfNC59w22w2ULC/J7au/oP8ylusuxncxizdR/+5UazzAlOWtkjzaABzzBWM4hEK";

        /** @var X509Certificate $proxy */
        $proxy = new X509CertificateLazyProxy($factory, $certData);
        $this->assertEquals(
            '-----BEGIN CERTIFICATE-----
MIIDXzCCAkegAwIBAgIJAM4CwNsdIhJ3MA0GCSqGSIb3DQEBBQUAMEYxDzANBgNV
BAMMBkVuZ2luZTERMA8GA1UECwwIU2VydmljZXMxEzARBgNVBAoMCk9wZW5Db25l
eHQxCzAJBgNVBAYTAk5MMB4XDTE0MDUxMjEzMjIxNloXDTI0MDUxMTEzMjIxNlow
RjEPMA0GA1UEAwwGRW5naW5lMREwDwYDVQQLDAhTZXJ2aWNlczETMBEGA1UECgwK
T3BlbkNvbmV4dDELMAkGA1UEBhMCTkwwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAw
ggEKAoIBAQC2aQ9OYsAASWR/aN5NB2mQqFsBc13uN0nSbjkk1Um8VouGo7OmSM0e
iur5my8UvPYth1DXQM6u2wiFq19RtfVZWJOmrzAfVHc9VRj9Xj4T+MVpR4bDWctv
xVT1OPm9L23KQKvaqDmUo7uSPsBD36EIH7dFOBydDtSXfZTW0ien+lZr6C4nPuxz
DbHJ+Jlo2brieimUBQNetX/ettnAglJ9536sJDkhsa120mkYPhVnvepbOtxPyU5Z
DUpDNmMQR2/SORCBJcfvLSVZ4It4O67l6/EJnkFRLerIqOpk/W8jY3USQaLM2WM7
sWBGxEFKDVcTFgrOH50Z94K2M/KweY2bAgMBAAGjUDBOMB0GA1UdDgQWBBSewI9O
zfzbIxnl6XMkaQkYY1hHPjAfBgNVHSMEGDAWgBSewI9OzfzbIxnl6XMkaQkYY1hH
PjAMBgNVHRMEBTADAQH/MA0GCSqGSIb3DQEBBQUAA4IBAQAFfYPZbsYPHz4ypV/a
O59do7CtHPnAMWr0NcQt4h9IW8gjihaNHt12V30QtHrVaXejXybB/LaGbPPyA64+
l/SeC7ksrRxlitCwFqnws6ISXJaYU0iEFHGUD/cAj1iGloIsOm5IOdb3sdG/SsBv
49G8es2wG0rDd0/s2fBVvXd4qUoXzKJAjYk1MFQxnGHomlt67SBrr2QLh+m2VHg+
mkdi6yrdm9B9ylF8V55Vl82pPZXxphIRgqdos5YWeALS7dr5dSw9s5smFBxyy8If
CQMxagfNC59w22w2ULC/J7au/oP8ylusuxncxizdR/+5UazzAlOWtkjzaABzzBWM
4hEK
-----END CERTIFICATE-----' . PHP_EOL,
            $proxy->toPem()
        );
    }
}
