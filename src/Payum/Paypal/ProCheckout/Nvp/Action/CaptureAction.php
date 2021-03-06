<?php
namespace Payum\Paypal\ProCheckout\Nvp\Action;

use Payum\Core\Action\PaymentAwareAction;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Core\Exception\LogicException;
use Payum\Core\Request\ObtainCreditCardRequest;
use Payum\Core\Security\SensitiveValue;
use Payum\Paypal\ProCheckout\Nvp\Api;
use Payum\Paypal\ProCheckout\Nvp\Bridge\Buzz\Request;
use Payum\Core\Request\CaptureRequest;

/**
 * @author Ton Sharp <Forma-PRO@66ton99.org.ua>
 */
class CaptureAction extends PaymentAwareAction implements ApiAwareInterface
{
    /**
     * @var Api
     */
    protected $api;

    /**
     * {@inheritdoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof Api) {
            throw new UnsupportedApiException('Not supported.');
        }

        $this->api = $api;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request CaptureRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = new ArrayObject($request->getModel());

        if (is_numeric($model['RESULT'])) {
            return;
        }

        $cardFields = array('ACCT', 'CVV2', 'EXPDATE');
        if (false == $model->validateNotEmpty($cardFields, false)) {
            try {
                $creditCardRequest = new ObtainCreditCardRequest;
                $this->payment->execute($creditCardRequest);
                $card = $creditCardRequest->obtain();

                $model['EXPDATE'] = new SensitiveValue($card->getExpireAt()->format('my'));
                $model['ACCT'] = $card->getNumber();
                $model['CVV2'] = $card->getSecurityCode();
            } catch (RequestNotSupportedException $e) {
                throw new LogicException('Credit card details has to be set explicitly or there has to be an action that supports ObtainCreditCardRequest request.');
            }
        }

        $buzzRequest = new Request();
        $buzzRequest->setFields($model->toUnsafeArray());
        $response = $this->api->doPayment($buzzRequest);
        
        $model->replace($response);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof CaptureRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}