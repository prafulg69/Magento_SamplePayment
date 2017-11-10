<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SamplePaymentGateway\Gateway\Http\Client\Axis;

use Magento\Payment\Gateway\Http\ClientInterface;
use Magento\Payment\Gateway\Http\TransferInterface;
use Magento\Payment\Model\Method\Logger;

class ClientMock implements ClientInterface
{
    const SUCCESS = 1;
    const FAILURE = 0;

    /**
     * @var array
     */
    private $results = [
        self::SUCCESS,
        self::FAILURE
    ];

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Logger $logger
     */
    public function __construct(
        Logger $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Places request to gateway. Returns result as ENV array
     *
     * @param TransferInterface $transferObject
     * @return array
     */
    public function placeRequest(TransferInterface $transferObject)
    {
        

        $response = $this->generateResponseForCode(
            $this->getResultCode(
                $transferObject
            )
        );
       $this->logger->debug(['testlog'=> 'Axis Hello !!!!!!']);
       $this->logger->debug(['HASH_KEY'=> $transferObject->getBody()['HASH_KEY']]);
       $this->logger->debug(
            [
                'request' => $transferObject->getBody(),
                'response' => $response
            ]
        );



        $conn = new VPCPaymentConnection();
        $secureSecret = $axisbank['hash_key'];
        $conn->setSecureSecret($secureSecret);
        $vpcURL =  Mage::getStoreConfig( 'payment/axisbank/submit_url' ) ;
        $gateway_data['vpc_Version']    = 1;
        $gateway_data['vpc_Command']    = "pay";
        $gateway_data['vpc_AccessCode'] = Mage::getStoreConfig( 'payment/axisbank/access_code' );
        $gateway_data['vpc_MerchTxnRef']= Mage::getSingleton( 'checkout/session' )->getLastRealOrderId();
        $gateway_data['vpc_Merchant']   = Mage::getStoreConfig( 'payment/axisbank/merchant_id' );
        $gateway_data['vpc_OrderInfo']  = "Eshop Axisbank Payment";
        $gateway_data['vpc_Amount']     = $finalamount;
        $gateway_data['vpc_ReturnURL']  = Mage::getBaseUrl() . 'axisbank/payment/response';
        $gateway_data['vpc_Locale']     = "en";
        $gateway_data['vpc_Currency']   ="INR";
     
        ksort ($gateway_data);
        foreach($gateway_data as $key => $value) {
              if (strlen($value) > 0) {
                //Mage::log('key '. $key. ' => '. $value);
                $conn->addDigitalOrderField($key, $value);
            }
        }
         
        $secureHash = $conn->hashAllFields();
        $conn->addDigitalOrderField("Title", "E-Shop Payment Gateway");
        $conn->addDigitalOrderField("vpc_SecureHash", $secureHash);
        $conn->addDigitalOrderField("vpc_SecureHashType", "SHA256");
        $vpcURL = $conn->getDigitalOrder($vpcURL);









        return $response;
    }

    /**
     * Generates response
     *
     * @return array
     */
    protected function generateResponseForCode($resultCode)
    {

        return array_merge(
            [
                'RESULT_CODE' => $resultCode,
                'TXN_ID' => $this->generateTxnId()
            ],
            $this->getFieldsBasedOnResponseType($resultCode)
        );
    }

    /**
     * @return string
     */
    protected function generateTxnId()
    {
        return md5(mt_rand(0, 1000));
    }

    /**
     * Returns result code
     *
     * @param TransferInterface $transfer
     * @return int
     */
    private function getResultCode(TransferInterface $transfer)
    {
        $headers = $transfer->getHeaders();

        if (isset($headers['force_result'])) {
            return (int)$headers['force_result'];
        }

        return $this->results[mt_rand(0, 1)];
    }

    /**
     * Returns response fields for result code
     *
     * @param int $resultCode
     * @return array
     */
    private function getFieldsBasedOnResponseType($resultCode)
    {
        switch ($resultCode) {
            case self::FAILURE:
                return [
                    'FRAUD_MSG_LIST' => [
                        'Stolen card',
                        'Customer location differs'
                    ]
                ];
        }

        return [];
    }
}
