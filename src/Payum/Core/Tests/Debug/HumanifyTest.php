<?php
namespace Payum\Core\Tests\Debug;

use Payum\Core\Debug\Humanify;
use Payum\Core\Request\CaptureRequest;
use Payum\Core\Request\Http\RedirectUrlInteractiveRequest;

class HumanifyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeAbstract()
    {
        $rc = new \ReflectionClass('Payum\Core\Debug\Humanify');

        $this->assertTrue($rc->isAbstract());
    }

    /**
     * @test
     */
    public function couldNotBeInstantiable()
    {
        $rc = new \ReflectionClass('Payum\Core\Debug\Humanify');

        $this->assertFalse($rc->isInstantiable());
    }

    /**
     * @test
     */
    public function shouldReturnObjectClassOnValueIfObjectPassed()
    {
        $this->assertEquals('HumanifyTest', Humanify::value($this));
    }

    /**
     * @test
     */
    public function shouldReturnObjectShortClassOnValueIfObjectPassedAndShortClassFlagSetTrue()
    {
        $this->assertEquals('HumanifyTest', Humanify::value($this, true));
    }

    /**
     * @test
     */
    public function shouldReturnObjectClassOnValueIfObjectPassedAndShortClassFlagSetFalse()
    {
        $this->assertEquals(__CLASS__, Humanify::value($this, false));
    }

    /**
     * @test
     */
    public function shouldReturnValueTypeIfNotObjectValueGivenOnValue()
    {
        $this->assertEquals('string', Humanify::value('foo'));
    }

    /**
     * @test
     */
    public function shouldReturnRequestTypeIfRequestNotObjectOnRequest()
    {
        $this->assertEquals('string', Humanify::request('foo'));
    }

    /**
     * @test
     */
    public function shouldReturnRequestShortClassIfRequestObjectOnRequest()
    {
        $this->assertEquals('HumanifyTest', Humanify::request($this));
    }

    /**
     * @test
     */
    public function shouldReturnRequestShortClassAndModelIfRequestImplementsModelRequestInterfaceOnRequest()
    {
        $request = new CaptureRequest($this);

        $this->assertEquals('CaptureRequest{model: HumanifyTest}', Humanify::request($request));
    }

    /**
     * @test
     */
    public function shouldReturnRequestShortClassAndUrlIfRedirectUrlInteractiveRequestOnRequest()
    {
        $request = new RedirectUrlInteractiveRequest('http://example.com/foo');

        $this->assertEquals('RedirectUrlInteractiveRequest{url: http://example.com/foo}', Humanify::request($request));
    }
}