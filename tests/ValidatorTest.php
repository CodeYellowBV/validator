<?php
namespace CodeYellow\Test\Validation;

use \CodeYellow\Api\Test\Validation\Mock\CustomRuleValidator;
use \CodeYellow\Api\Test\Validation\Mock\SimpleValidator;
use \CodeYellow\Api\Test\Validation\Mock\AllTypeValidator;
use \CodeYellow\Api\Test\Validation\Mock\ExistsValidator;
use \CodeYellow\Api\Test\Validation\Mock\RequiredValidator;
use \Symfony\Component\Translation\TranslatorInterface;

/**
 * @group validator
 */
class ValidatorTest extends \PHPUnit\Framework\TestCase
{

    /**
     * Returns test cases that are valid.
     */
    public function okProvider()
    {
        return [
            [['age' => 10]],
            [[
                'age' => 10,
                'size' => 20
            ]]
        ];
    }

    /**
     * Returns test cases that are not Valid.
     */
    public function notOkProvider()
    {
        return [
            [[]],
            [[
                'age' => 3,
                'size' => 20
            ]]
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
        $validator = new SimpleValidator($this->getTranslator(), $this->getPresenceVerifier());
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
        $validator = new SimpleValidator($this->getTranslator(), $this->getPresenceVerifier());
        // age is too low
        $this->assertFalse(
            $validator->verify($testCase)
        );
    }



    /**
     * Tests if the parse method returns ok if the data is
     * correct.
     *
     * @dataProvider okProvider
     */
    public function testParseOk($testCase)
    {
        $validator = new SimpleValidator($this->getTranslator(), $this->getPresenceVerifier());
        $toParse = $testCase;
        $returned = $validator->parse($toParse);

        // Only assertEquals, since the testcases may contain values in another type
        // e.g. ['age' => '10']. This will not be the same when parsed
        $this->assertEquals($testCase, $toParse);
        $this->assertEquals($testCase, $returned);
    }
    


    /**
     * Tests if the parse method returns ok if the data is
     * correct.
     *
     * @dataProvider notOkProvider
     * @expectedException CodeYellow\Validation\Exception
     */
    public function testParseNotOk($testCase)
    {
        $validator = new SimpleValidator($this->getTranslator(), $this->getPresenceVerifier());
        $validator->parse($testCase);
    }

    /**
     * Tests if the parse method changes the datatype to the correct datatype.
     */
    public function testParseChangeDataType()
    {
        $validator = new SimpleValidator($this->getTranslator(), $this->getPresenceVerifier());

        $testCase = [
                'age' => '10',
                'size' => '20'
        ];

        $validator->parse($testCase);

        // Type must be changed, so assertSame
        $this->assertSame(10, $testCase['age']);
    }

     /**
     * Tests if the parse method filters bogus data
     */
    public function testParseFiltersData()
    {
        $validator = new SimpleValidator($this->getTranslator(), $this->getPresenceVerifier());

        $testCase = [
                'age' => 10,
                'non_existant' => 14
        ];

        $validator->parse($testCase);

        // Age still needs to be an integer, so assertSame
        $this->assertSame(['age' => 10], $testCase);
    }


    /**
     * Tests if the custom rule validation works.
     */
    public function testCustomRule()
    {
        $validator = new CustomRuleValidator($this->getTranslator(), $this->getPresenceVerifier());

        $this->assertTrue($validator->verify([
                'age' => 9
        ]));

        $this->assertFalse($validator->verify([
                'age' => 10
        ]));
    }

    /**
     * returns testcases that should be a valid boolean
     * test cases consist of the tupe [$testcase, $expectedbooleanvalue].
     */
    public function booleanOkProvider()
    {
        return [
            [true, true],
            [false, false],
            [1, true],
            [0, false],
            ['1', true],
            ['0', false]
        ];
    }

    /**
     * Test if the validateBoolean works, and the attribute is cast
     * to a boolean.
     * @dataProvider booleanOkProvider
     */
    public function testValidateBooleanOk($input, $expectedResult)
    {
        $validator = new AllTypeValidator($this->getTranslator(), $this->getPresenceVerifier());
        $params = ['boolean' => $input];
        $validator->parse($params);

        // use assertSame because it has to be a boolean
        $this->assertSame($expectedResult, $params['boolean']);
    }

    /**
     * Test if verifies fails if a boolean is not ok.
     */
    public function testValidateBooleanNotOk()
    {
        $validator = new AllTypeValidator($this->getTranslator(), $this->getPresenceVerifier());
        $params = ['boolean' => '12'];
        $this->assertFalse($validator->verify($params));
    }

    /**
     * returns testcases that should be a valid integer
     * test cases consist of the tupe [$testcase, $expectedbooleanvalue].
     */
    public function integerOkProvider()
    {
        return [
            [13, 13],
            ['13', 13],
        ];
    }

    /**
     * Test if validateInteger works and the attribute is cast to
     * an integer.
     *
     * @dataProvider IntegerOkProvider
     */
    public function testValidateIntegerOk($input, $expectedResult)
    {
        $validator = new AllTypeValidator($this->getTranslator(), $this->getPresenceVerifier());
        $params = ['integer' => $input];
        $validator->parse($params);

        // use assertSame because it has to be a boolean
        $this->assertSame($expectedResult, $params['integer']);
    }

    /**
     * Test if verifies fails if a integer is not ok.
     */
    public function testValidateIntegerNotOk()
    {
        $validator = new AllTypeValidator($this->getTranslator(), $this->getPresenceVerifier());
        $params = ['integer' => 'string'];
        $this->assertFalse($validator->verify($params));
    }

    /**
     * returns testcases that should be a valid integer
     * test cases consist of the tupe [$testcase, $expectedbooleanvalue].
     */
    public function numericOkProvider()
    {
        // Test cases from http://php.net/manual/en/function.is-numeric.php
        return [
            [42, 42],
            ["13", 13],
            [0x539, 1337],
            ['0x539', 1337],
            [02471, 1337],
            ['02471', 1337],
            [0b10100111001, 1337],
            ['0b10100111001', 1337],
            [1337e0, 1337],
            ['1337e0', 1337],
            ['9.1', 9.1],
        ];
    }

    /**
     * Test if validateInteger works and the attribute is cast to
     * an integer.
     *
     * @dataProvider IntegerOkProvider
     */
    public function testValidateNumericOk($input, $expectedResult)
    {
        $validator = new AllTypeValidator($this->getTranslator(), $this->getPresenceVerifier());
        $params = ['numeric' => $input];
        $validator->parse($params);

        // use assertSame because it has to be a boolean
        $this->assertSame($expectedResult, $params['numeric']);
    }

    /**
     * Test if verifies fails if a integer is not ok.
     */
    public function testValidateNumericNotOk()
    {
        $validator = new AllTypeValidator($this->getTranslator(), $this->getPresenceVerifier());
        $params = ['numeric' => 'string'];
        $this->assertFalse($validator->verify($params));
    }

    /**
     * Provides all kinds of stuff that is not a string.
     * @see http://php.net/manual/en/function.is-string.php
     */
    public function noStringProvider()
    {
        return [
            [false],
            [true],
            [23],
            [23.5],
            [0]
        ];
    }

    /**
     * Test if a numerical value as string is not ok for the validator.
     *
     * @dataProvider noStringProvider
     */
    public function testValidateStringNotOk($noString)
    {
        $validator = new AllTypeValidator($this->getTranslator(), $this->getPresenceVerifier());
        $params = ['string' => $noString];
        $this->assertFalse($validator->verify($params));
    }

    /**
     * Test if a numerical value as string is not ok for the pase method.
     *
     * @dataProvider noStringProvider
     * @expectedException \CodeYellow\Validation\Exception
     */
    public function testParseStringNotOk($noString)
    {
        $validator = new AllTypeValidator($this->getTranslator(), $this->getPresenceVerifier());
        $params = ['string' => $noString];
        $validator->parse($params);
    }

    /**
     * Test if the validator verify does not give an
     * RuntimeException.
     */
    public function testExistsWorks()
    {
        $validator = new ExistsValidator($this->getTranslator(), $this->getPresenceVerifier());
        $params = ['id' => 1];

        $this->assertTrue($validator->verify($params));
    }

    /**
     * Test if a validator with only required works.
     * @see http://phabricator.intern/T2562
     */
    public function testRequiredWorks()
    {
        $validator = new RequiredValidator($this->getTranslator(), $this->getPresenceVerifier());

        $this->assertTrue($validator->verify(['username' => 'test2']));
    }
}
