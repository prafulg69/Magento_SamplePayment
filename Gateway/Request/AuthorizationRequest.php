<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SamplePaymentGateway\Gateway\Request;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\Encryption\EncryptorInterface;

class AuthorizationRequest implements BuilderInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;



    /**
     * @param ConfigInterface $config
     */
    public function __construct(
        ConfigInterface $config,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        $this->config = $config;
        $this->encryptor = $encryptor;
    }

    /**
     * Builds ENV request
     *
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        /** @var PaymentDataObjectInterface $payment */
        $payment = $buildSubject['payment'];
        $order = $payment->getOrder();
        $address = $order->getShippingAddress();

        return [
            'TXN_TYPE' => 'A',
            'INVOICE' => $order->getOrderIncrementId(),
            'AMOUNT' => $order->getGrandTotalAmount(),
            'CURRENCY' => $order->getCurrencyCode(),
            'EMAIL' => $address->getEmail(),
            'MERCHANT_KEY' => $this->config->getValue(
                'merchant_gateway_key',
                $order->getStoreId()
            ),
             'MERCHANT_ID' =>$this->encryptor->decrypt($this->config->getValue(
                'axis_merchant_id',
                $order->getStoreId()
            )),
              'ACCESS_CODE' =>$this->encryptor->decrypt($this->config->getValue(
                'axis_access_code',
                $order->getStoreId()
            )),
            'HASH_KEY' =>$this->encryptor->decrypt($this->config->getValue(
                'axis_hash_key',
                $order->getStoreId()
            )),
             'GATEWAY_URL' =>$this->encryptor->decrypt($this->config->getValue(
                'axis_gateway_url',
                $order->getStoreId()
            )),

        ];
    }
}
