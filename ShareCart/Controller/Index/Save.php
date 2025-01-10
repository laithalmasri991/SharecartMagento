<?php

namespace Laith\ShareCart\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Quote\Model\QuoteFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlInterface;

class Save extends Action
{
    protected $resultJsonFactory;
    protected $quoteFactory;
    protected $jsonSerializer;
    protected $url;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        QuoteFactory $quoteFactory,
        Json $jsonSerializer,
        UrlInterface $url
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->quoteFactory = $quoteFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->url = $url;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();

        try {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->info('Entering Save Controller');

            // Retrieve the current quote
            $quote = $this->_getQuote();
            $this->_objectManager->get('Psr\Log\LoggerInterface')->info('Quote Retrieved: ' . $quote->getId());

            // Serialize cart items
            $cartData = $this->jsonSerializer->serialize($quote->getAllVisibleItems());
            $this->_objectManager->get('Psr\Log\LoggerInterface')->info('Cart Data Serialized: ' . $cartData);

            // Generate a unique token for the shared cart
            $token = bin2hex(random_bytes(16));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->info('Token Generated: ' . $token);

            // Save shared cart data into the database
            $connection = $this->_objectManager->get('Magento\Framework\App\ResourceConnection')->getConnection();
            $connection->insert(
                'shared_cart',
                [
                    'token' => $token,
                    'cart_data' => $cartData,
                    'created_at' => (new \DateTime())->format('Y-m-d H:i:s')
                ]
            );
            $this->_objectManager->get('Psr\Log\LoggerInterface')->info('Data Inserted into shared_cart');

            // Generate the shareable link
            $link = $this->url->getUrl('sharecart/index/load', ['token' => $token]);
            $this->_objectManager->get('Psr\Log\LoggerInterface')->info('Shareable Link Generated: ' . $link);

            return $result->setData(['success' => true, 'link' => $link]);

        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical('Error: ' . $e->getMessage());
            return $result->setData(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function _getQuote()
    {
        return $this->_objectManager->get('Magento\Checkout\Model\Session')->getQuote();
    }
}
