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
        return $containerMock;
    }

    /**
     * @return \Symfony\Component\Translation\TranslatorInterface
     */
    protected function getTranslator()
    {
        $translatorMock = m::mock('Symfony\Component\Translation\TranslatorInterface');
        $translatorMock->shouldReceive('trans')->andReturn('bogus translation');
        $translatorMock->shouldReceive('get')->andReturn('bogus translation ');

        return $translatorMock;
    }

    /**
     * @return \Illuminate\Validation\PresenceVerifierInterface
     */
    protected function getPresenceVerifier()
    {
        $presenceMock = m::mock('Illuminate\Validation\PresenceVerifierInterface');
        $presenceMock->shouldReceive('setConnection');
        return $presenceMock;
    }
}