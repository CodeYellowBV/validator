<?php
namespace CodeYellow\Test\Validation;

use CodeYellow\Api\Test\Validation\Mock\NestedCollectionValidator;

class NestedCollectionValidatorTest extends BaseTest
{
    /**
     * Returns test cases that are valid.
     */
    public function okProvider()
    {
        return [
            [
                [
                    'nested' => [   // empty collection is ok
                    ]
                ],
            ],
            [
                [
                    'nested' => [   // collection with one valid item is ok
                        [
                            'age' => 10,
                            'size' => 15
                        ]
                    ]
                ],
            ],
            [
                [
                    'nested' => [ // collection with two valid items is ok
                        [
                            'age' => 10,
                            'size' => 15
                        ],
                        [
                            'age' => 10,
                            'size' => 15
                        ]
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
                    'nested' => [
                        [   // one element and not ok
                            'age' => 0,
                            'size' => 0
                        ],
                    ]
                ],
            ],
            [
                [
                    'nested' => [
                        [   // one element ok, one element not ok
                            'age' => 15,
                            'size' => 10
                        ],
                        [   // one element ok, one element not ok
                            'age' => 0,
                            'size' => 0
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
        $validator = new NestedCollectionValidator($this->getTranslator(), $this->getPresenceVerifier(), $this->getContainer());
        $validator->verify($testCase);


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

        $validator = new NestedCollectionValidator($this->getTranslator(), $this->getPresenceVerifier(), $this->getContainer());


        // age is too low
        $this->assertFalse(
            $validator->verify($testCase)
        );
    }

    /**
     * Test that the validation message is correct, i.e. it prefixes the nested validation error with the key of
     * the nested object
     */
    public function testMessage()
    {
        $validator = new NestedCollectionValidator($this->getTranslator(), $this->getPresenceVerifier(), $this->getContainer());
        $validator->verify([
            'nested' => [
                [   // one element ok, one element not ok
                    'age' => 0,
                    'size' => 0
                ],
                [   // one element ok, one element not ok
                    'age' => 0,
                    'size' => 20 // this element is ok
                ],
            ]
        ]);
        

        $failures = $validator->failed();

        // Failures need to be set correctly
        $this->assertCount(3, $failures); // One attribute has failed
        $this->assertArrayHasKey('nested.0.age', $failures); // namely age
        $this->assertArrayHasKey('nested.0.size', $failures); // and size
        $this->assertArrayHasKey('nested.1.age', $failures); // and size

        // Messages need to be set correctly
        $messages = $validator->getMessageBag()->getMessages();

        $this->assertcount(3, $messages); // Only messages for nested
        $this->assertArrayHasKey('nested.0.age', $messages);
        $this->assertArrayHasKey('nested.0.size', $messages);
        $this->assertArrayHasKey('nested.1.age', $messages);
        $this->assertArrayNotHasKey('nested.1.size', $messages);
        $this->assertCount(1, $messages['nested.0.age']);
        $this->assertCount(1, $messages['nested.0.size']);
        $this->assertCount(1, $messages['nested.1.age']);
        $this->assertTrue(is_string($messages['nested.0.size'][0]));
        $this->assertTrue(is_string($messages['nested.0.age'][0]));
        $this->assertTrue(is_string($messages['nested.1.age'][0]));
    }
}
