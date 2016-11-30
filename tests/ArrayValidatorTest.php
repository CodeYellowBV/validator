<?php
namespace CodeYellow\Test\Validation;


use \CodeYellow\Validation\ArrayValidator;
use \CodeYellow\Validation\Validator;
use \Illuminate\Support\MessageBag;
use Mockery as m;

/**
 * @group arrayValidator
 */
class ArrayValidatorTest extends BaseTest
{
    /**
     * Returns an arrayvalidator object
     */
    private function getArrayValidator()
    {
        return new ArrayValidator(
            new Validator(
                $this->getTranslator(),
                $this->getPresenceVerifier(),
                $this->getContainer()
            )
        );
    }

    /**
     * Returns test cases that are valid
     * Returned testcases are tuple ($params, $key, $rules)
     */
    public function okProvider()
    {
        return [
            [['name' => 'test'], 'name', 'required|string'],
            [[], 'name', 'string'],
            [['foo' => 'bar', 'baz' => 0], 'baz', 'integer']
        ];
    }

     /**
     * Returns test cases that are not valid
     * Returned testcases are tuple ($params, $key, $rules)
     */
    public function notOkProvider()
    {
        return [
            [[], 'name', 'required'],
            [['foo' => 'bar', 'baz' => 0], 'baz', 'string']
        ];
    }

    /**
     * Test if the verify method is ok for the test cases
     * that should be ok.
     *
     * @dataProvider okProvider
     * @group fuckTaylor
     */
    public function testVerifyOk($params, $key, $rules)
    {
        $arrayValidator = $this->getArrayValidator();
        $this->assertTrue(
            $arrayValidator->verify($params, $key, $rules)
        );
    }

    /**
     * Test if the verify method returns not ok for the test
     * cases that should not be ok
     *
     * @dataProvider notOkProvider
     */
    public function testVerifyNotOk($params, $key, $rules)
    {
        $arrayValidator = $this->getArrayValidator();
        $this->assertFalse(
            $arrayValidator->verify($params, $key, $rules)
        );
    }

    /**
     * Test if a messagebag is created when the test is nog ok
     *
     * @dataProvider notOkProvider
     */
    public function testNewMessageBagNotOk($params, $key, $rules)
    {
        $arrayValidator = $this->getArrayValidator();
        $messagebag = null;
        $arrayValidator->verify($params, $key, $rules, $messagebag);

        $this->assertInstanceOf('\Illuminate\Support\MessageBag', $messagebag);

        // Count must return an integer, so use assertSame
        $this->assertSame(1, $messagebag->count());
    }

    /**
     * Test if a messagebag is appended when the test is nog ok
     *
     * @dataProvider notOkProvider
     */
    public function testAppendMessageBagNotOk($params, $key, $rules)
    {
        $arrayValidator = $this->getArrayValidator();
        $messagebag = new MessageBag();
        $messagebag->add('test', 'test');
        $arrayValidator->verify($params, $key, $rules, $messagebag);

        $this->assertInstanceOf('\Illuminate\Support\MessageBag', $messagebag);

        // Count must return an integer, so use assertSame
        $this->assertSame(2, $messagebag->count());

        // Message must be exactly the same as the provided message
        $this->assertSame('test', $messagebag->get('test')[0]);
    }


    /**
     * Test if a messagebag is created when the test are ok
     *
     * @dataProvider okProvider
     */
    public function testNewMessageBagOk($params, $key, $rules)
    {
        $arrayValidator = $this->getArrayValidator();
        $messagebag = null;
        $arrayValidator->verify($params, $key, $rules, $messagebag);

        $this->assertInstanceOf('\Illuminate\Support\MessageBag', $messagebag);

        // Count must return an integer, so use assertSame
        $this->assertSame(0, $messagebag->count());
    }

    /**
     * Test if a messagebag is appended when the test is nog ok
     *
     * @dataProvider okProvider
     */
    public function testAppendMessageBagOk($params, $key, $rules)
    {
        $arrayValidator = $this->getArrayValidator();
        $messagebag = new MessageBag();
        $messagebag->add('test', 'test');
        $arrayValidator->verify($params, $key, $rules, $messagebag);

        $this->assertInstanceOf('\Illuminate\Support\MessageBag', $messagebag);

        // Count must return an integer, so use assertSame
        $this->assertSame(1, $messagebag->count());

        // Message must be exactly the same as the provided message
        $this->assertSame('test', $messagebag->get('test')[0]);
    }

    /**
     * Test if the get works if the test case is ok
     *
     * @dataProvider okProvider
     */
    public function testGetOk($params, $key, $rules)
    {
        $arrayValidator = $this->getArrayValidator();
        $returned = $arrayValidator->get($params, $key, $rules);


        if (isset($params[$key])) {
            // N.b. non strict comparison, since the key might be tampered
            $this->assertEquals($params[$key], $returned);
        } else {
            $this->assertNull($returned);
        }
    }


    /**
     * Test if the get returns an error if the test is not ok
     *
     * @dataProvider notOkProvider
     * @expectedException \CodeYellow\Validation\Exception
     */
    public function testGetNotOk($params, $key, $rules)
    {
        $arrayValidator = $this->getArrayValidator();
        $returned = $arrayValidator->get($params, $key, $rules);
    }

    /**
     * Test if the get transforms the variable that is given
     * e.g. '42' => 42 if it has to be numeric
     */
    public function testGetOkChangesType()
    {
        $params = ['key' => '42'];
        $key = 'key';
        $rules = 'numeric';

        $arrayValidator = $this->getArrayValidator();
        $returned = $arrayValidator->get($params, $key, $rules);

        // Use same, since it has to be an integer
        $this->assertSame(42, $returned);
    }

    /**
     * Test if the exists works on the arrayvalidator
     */
    public function testExistsWorks()
    {
        $arrayValidator = $this->getArrayValidator();
        $params['customer_id'] = 1;
        $customerId = $arrayValidator->get($params, 'customer_id', 'exists:customers,id');

        // It still must be an integer, so assertSame
        $this->assertSame(1, $customerId);
    }

    public function primitiveProvider()
    {
        return [
            [
                ['foo' => 'string'], 'foo', ['foo' => 'numeric'], false,
            ],
            [
                [], 'foo', ['foo' => 'numeric'], true,
            ],
            [
                ['foo' => null], 'foo', 'array', false,
            ],
            [
                ['foo' => ''], 'foo', 'array', false,
            ],
            [
                [], 'foo',  'array', true,
            ],
            [
                ['foo' => null], 'foo', 'string', false,
            ],
        ];
    }

    /**
     * Test that an empty array is NOT a valid string.
     * Code copy/pasted from: https://github.com/laravel/framework/commit/980d098ad091a5087a93202ebd4c091e336f3e58
     * @see https://github.com/laravel/framework/issues/13005
     * @dataProvider primitiveProvider
     */
    public function testThatPrimitiveTypesAreImplicitAndMustBeCorrectIfDataIsPresent($params, $key, $rules, $expectedResult)
    {
        $arrayValidator = $this->getArrayValidator();
        $actualResult = $arrayValidator->verify($params, $key, $rules);
        $this->assertSame($expectedResult, $actualResult);
    }
}
