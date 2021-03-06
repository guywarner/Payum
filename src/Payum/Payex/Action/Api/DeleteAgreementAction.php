<?php
namespace Payum\Payex\Action\Api;

use Payum\Core\Action\ActionInterface;
use Payum\Core\ApiAwareInterface;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Payex\Api\AgreementApi;
use Payum\Core\Exception\LogicException;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\Exception\UnsupportedApiException;
use Payum\Payex\Request\Api\CheckAgreementRequest;
use Payum\Payex\Request\Api\CompleteOrderRequest;
use Payum\Payex\Request\Api\CreateAgreementRequest;
use Payum\Payex\Request\Api\DeleteAgreementRequest;

class DeleteAgreementAction implements ActionInterface, ApiAwareInterface
{
    /**
     * @var AgreementApi
     */
    protected $api;
    
    /**
     * {@inheritDoc}
     */
    public function setApi($api)
    {
        if (false == $api instanceof AgreementApi) {
            throw new UnsupportedApiException('Expected api must be instance of AgreementApi.');
        }
        
        $this->api = $api;
    }
    
    /**
     * {@inheritDoc}
     */
    public function execute($request)
    {
        /** @var $request DeleteAgreementRequest */
        if (false == $this->supports($request)) {
            throw RequestNotSupportedException::createActionNotSupported($this, $request);
        }

        $model = ArrayObject::ensureArrayObject($request->getModel());

        $model->validateNotEmpty(array(
            'agreementRef',
        ));

        $result = $this->api->delete((array) $model);

        $model->replace($result);
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return 
            $request instanceof DeleteAgreementRequest &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}