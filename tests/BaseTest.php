<?php
namespace CodeYellow\Test\Validation;


use Mockery as m;

/**
 * @group arrayValidator
 */
abstract class BaseTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @return \Illuminate\Contracts\Container\Container
     */
    protected function getContainer()
    {
        $containerMock = m::mock('Illuminate\Contracts\Container\Container');

        $makeClass = '';
        // We assume that we always have a validator
        $containerMock->shouldReceive('make')->with(\Mockery::on(function ($class) use (&$makeClass) {
            $makeClass = $class;

            return strlen($class) > 0;
        }))->andReturnUsing(function () use (&$makeClass) {
            return new $makeClass(
                $this->getTranslator(),
                $this->getPresenceVerifier(),
                $this->getContainer()
            );
        });
        return $containerMock;
    }

    /**
     * @return \Symfony\Component\Translation\TranslatorInterface
     */
    protected function getTranslator()
    {
        $translatorMock = m::mock('Symfony\Component\Translation\TranslatorInterface');

        $translation = '';

        $translatorMock->shouldReceive('trans')->with(\Mockery::on(function ($str) use (&$translation) {
            $translation = $str;
            return true;
        }))->andReturnUsing(function () use (&$translation) {
            return $translation;
        });
        $translatorMock->shouldReceive('get')->with(\Mockery::on(function ($str) use (&$translation) {
            $translation = $str;
            return true;
        }))->andReturnUsing(function () use (&$translation) {
            return $translation;
        });
        return $translatorMock;
    }

    private $existsValue = false;

    /**
     * Set if the exists validator is called, wether it should return true or false
     * @param $value
     */
    protected function setExistsValue($value)
    {
        $this->existsValue = $value;
    }

    /**
     * @return \Illuminate\Validation\PresenceVerifierInterface
     */
    protected function getPresenceVerifier()
    {
        $presenceMock = m::mock('Illuminate\Validation\PresenceVerifierInterface');
        $presenceMock->shouldReceive('setConnection');
        $presenceMock->shouldReceive('getCount')->andReturnUsing(function () {
            return $this->existsValue ? 1 : 0;
        });
        return $presenceMock;
    }
}