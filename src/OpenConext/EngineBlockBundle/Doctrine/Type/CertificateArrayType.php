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


namespace OpenConext\EngineBlockBundle\Doctrine\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;
use OpenConext\EngineBlock\Exception\InvalidArgumentException;
use OpenConext\EngineBlock\Metadata\X509\X509CertificateFactory;
use OpenConext\EngineBlock\Metadata\X509\X509CertificateLazyProxy;
use TypeError;

class CertificateArrayType extends Type
{
    const NAME = 'engineblock_certificate_array';

    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform): string
    {
        return $platform->getJsonTypeDeclarationSQL($fieldDeclaration);
    }

    /**
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (is_null($value)) {
            return null;
        }

        if (!is_array($value)) {
            throw new ConversionException(
                sprintf(
                    'Value "%s" must be null or an instance of %s (%s) to be able to ' .
                    'convert it to a database value',
                    $value,
                    $this->getName(),
                    "null, array"
                )
            );
        }

        if (count($value) == 0) {
            return null;
        }

        $certificates = [];
        foreach ($value as $certificate) {
            if (!$certificate instanceof X509CertificateLazyProxy) {
                throw new ConversionException(
                    sprintf(
                        'Value "%s" must be null or an instance of %s (%s) to be able to ' .
                        'convert it to a database value',
                        $certificate,
                        $this->getName(),
                        X509CertificateLazyProxy::class
                    )
                );
            }
            array_push($certificates, $certificate->toCertData());
        }

        return json_encode($certificates);
    }

    /**
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): array
    {
        if (is_null($value)) {
            return [];
        }

        try {
            $certificates = [];
            $decoded = json_decode($value, true);

            if (!is_array($decoded)) {
                throw new ConversionException(
                    sprintf(
                        'Could not convert database value "%s" to Doctrine Type %s. Expected format: %s',
                        $decoded,
                        $this->getName(),
                        "array"
                    )
                );
            }

            foreach ($decoded as $certificate) {
                if (is_string($certificate)) {
                    array_push($certificates, new X509CertificateLazyProxy(new X509CertificateFactory(), $certificate));
                } else {
                    throw new ConversionException(
                        sprintf(
                            'Could not convert database value "%s" to Doctrine Type %s. Expected format: %s',
                            $certificate,
                            $this->getName(),
                            "X509Certificate"
                        )
                    );
                }
            }
        } catch (InvalidArgumentException $e) {
            throw new ConversionException(
                sprintf(
                    'Could not convert database value "%s" to Doctrine Type %s. Expected format: %s',
                    $value,
                    $this->getName(),
                    X509CertificateLazyProxy::class
                ),
                0,
                $e
            );
        }

        return $certificates;
    }

    public function getName(): string
    {
        return self::NAME;
    }
}
