<?php
class EngineBlock_Test_TypeTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider validAttributesProvider
     *
     * @param $attributeName
     * @param $options
     * @param $attributes
     */
    public function testAttributeValidates($attributeName, $options, $attributes)
    {
        $validator = new EngineBlock_Attributes_Validator_Type($attributeName, $options);
        $this->assertTrue($validator->validate($attributes));
    }

    public function validAttributesProvider()
    {
        return array(
            array(
                'attributeName' => 'foo',
                'options' => 'URN',
                'attributes' => array(
                    'foo' => array(
                        'urn:mace:dir:entitlement:common-lib-terms'
                    )
                )
            ),
            array(
                'attributeName' => 'foo',
                'options' => 'URL',
                'attributes' => array(
                    'foo' => array(
                        'http://example.com'
                    )
                )
            ),
            array(
                'attributeName' => 'foo',
                'options' => 'URI',
                'attributes' => array(
                    'foo' => array(
                        '?'
                    )
                )
            )
        );
    }
}
