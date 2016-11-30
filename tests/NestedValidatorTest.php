<?php
namespace CodeYellow\Test\Validation;

use CodeYellow\Api\Test\Validation\Mock\NestedValidator;

class NestedValidatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Returns test cases that are valid.
     */
    public function okProvider()
    {
        return [
            [
                [
                    'nested' => [
                        'age' => 10,
                        'size' => 15
                    ]

                ]
            ],
        ];
    }

    /**
     * Returns test cases that are not Valid.
     */
    public function notOkProvider()
    {
        return [
            [
                [
                    []  // No nested is not ok
                ]
            ],
            [
                [
                    [
                        'nested' => [] // Nested must be set
                    ]
                ],
            ],
            [
                [
                    [
                        'nested' => [   // not ok
                            'age' => 15,
                            'size' => 8
                        ],
                    ]
                ],
            ]
        ];
    }

    /**
     * Test that the verify method returns true if
     * the validation is ok.
     *
     * @dataProvider okProvider
     */
    public function testVerifyOk($testCase)
    {
        $validator = new NestedValidator($this->getTranslator(), $this->getPresenceVerifier());
        $this->assertTrue($validator->verify($testCase));
    }

    /**
     * Tests that the verify method returns false if
     * the validation is not ok.
     *
     * @dataProvider notOkProvider
     */
    public function testVerifyNotOk($testCase)
    {
        $validator = new NestedValidator($this->getTranslator(), $this->getPresenceVerifier());
        // age is too low
        $this->assertFalse(
            $validator->verify($testCase)
        );
    }

    /**
     * Test that the validation message is correct, i.e. it prefixes the nested validation error with the key of
     * the nested object
     *
     * @param $testCase
     */
    public function testMessage()
    {
        $validator = new NestedValidator($this->getTranslator(), $this->getPresenceVerifier());
        $validator->verify([
            'nested' => [   // not ok
                'age' => 1,
                'size' => 2
            ]
        ]);

        $failures = $validator->failed();


        // Failures need to be set correctly
        $this->assertCount(2, $failures); // One attribute has failed
        $this->assertArrayHasKey('nested.age', $failures); // which is the nested attribute
        $this->assertArrayHasKey('nested.size', $failures); // and size

        // Messages need to be set correctly
        $messages = $validator->getMessageBag()->getMessages();

        $this->assertcount(2, $messages); // Only messages for nested
        $this->assertArrayHasKey('nested.age', $messages);
        $this->assertArrayHasKey('nested.size', $messages);
        $this->assertCount(1, $messages['nested.age']);
        $this->assertCount(1, $messages['nested.size']);
        $this->assertTrue(is_string($messages['nested.size'][0]));
        $this->assertTrue(is_string($messages['nested.age'][0]));
    }
}
