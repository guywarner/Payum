<?php
namespace Payum\Klarna\Checkout\Tests\Action;

use Payum\Core\PaymentInterface;
use Payum\Core\Request\SyncRequest;
use Payum\Klarna\Checkout\Action\SyncAction;
use Payum\Klarna\Checkout\Constants;
use Payum\Klarna\Checkout\Request\Api\FetchOrderRequest;

class SyncActionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function shouldBeSubClassOfPaymentAwareAction()
    {
        $rc = new \ReflectionClass('Payum\Klarna\Checkout\Action\SyncAction');

        $this->assertTrue($rc->isSubclassOf('Payum\Core\Action\PaymentAwareAction'));
    }

    /**
     * @test
     */
    public function couldBeConstructedWithoutAnyArguments()
    {
        new SyncAction;
    }

    /**
     * @test
     */
    public function shouldSupportSyncRequestWithArrayAsModel()
    {
        $action = new SyncAction();

        $this->assertTrue($action->supports(new SyncRequest(array())));
    }

    /**
     * @test
     */
    public function shouldNotSupportAnythingNotSyncRequest()
    {
        $action = new SyncAction;

        $this->assertFalse($action->supports(new \stdClass()));
    }

    /**
     * @test
     */
    public function shouldNotSupportSyncRequestWithNotArrayAccessModel()
    {
        $action = new SyncAction;

        $this->assertFalse($action->supports(new SyncRequest(new \stdClass)));
    }

    /**
     * @test
     *
     * @expectedException \Payum\Core\Exception\RequestNotSupportedException
     */
    public function throwIfNotSupportedRequestGivenAsArgumentOnExecute()
    {
        $action = new SyncAction;

        $action->execute(new \stdClass());
    }

    /**
     * @test
     */
    public function shouldSubExecuteFetchOrderRequestIfModelHasLocationSet()
    {
        $orderMock = $this->getMock('Klarna_Checkout_Order', array('marshal'), array(), '', false);
        $orderMock
            ->expects($this->once())
            ->method('marshal')
            ->will($this->returnValue(array('foo' => 'fooVal', 'bar' => 'barVal')))
        ;

        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->once())
            ->method('execute')
            ->with($this->isInstanceOf('Payum\Klarna\Checkout\Request\Api\FetchOrderRequest'))
            ->will($this->returnCallback(function(FetchOrderRequest $request) use ($orderMock) {
                $request->setOrder($orderMock);
            }))
        ;

        $action = new SyncAction;
        $action->setPayment($paymentMock);

        $request = new SyncRequest(array(
            'status' => Constants::STATUS_CHECKOUT_INCOMPLETE,
            'location' => 'theLocation',
        ));

        $action->execute($request);

        $model = $request->getModel();
        $this->assertEquals('theLocation', $model['location']);
        $this->assertEquals(Constants::STATUS_CHECKOUT_INCOMPLETE, $model['status']);
        $this->assertEquals('fooVal', $model['foo']);
        $this->assertEquals('barVal', $model['bar']);
    }

    /**
     * @test
     */
    public function shouldDoNothingIfModelHasNotLocationSet()
    {
        $paymentMock = $this->createPaymentMock();
        $paymentMock
            ->expects($this->never())
            ->method('execute')
        ;

        $action = new SyncAction;
        $action->setPayment($paymentMock);

        $request = new SyncRequest(array());

        $action->execute($request);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PaymentInterface
     */
    protected function createPaymentMock()
    {
        return $this->getMock('Payum\Core\PaymentInterface');
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Klarna_Checkout_Order
     */
    protected function createOrderMock()
    {
        return $this->getMock('Klarna_Checkout_Order', array(), array(), '', false);
    }
}