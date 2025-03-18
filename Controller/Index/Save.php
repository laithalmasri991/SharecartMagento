<?php

namespace Laith\Magentosharecart\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ResourceConnection;
use Magento\Checkout\Model\Session;

class Save extends Action
{
    protected $resultJsonFactory;
    protected $jsonSerializer;
    protected $resourceConnection;
    protected $checkoutSession;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Json $jsonSerializer,
        ResourceConnection $resourceConnection,
        Session $checkoutSession
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->resourceConnection = $resourceConnection;
        $this->checkoutSession = $checkoutSession;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->resultJsonFactory->create();
            // Prevent caching by Varnish
            $this->getResponse()->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $this->getResponse()->setHeader('Pragma', 'no-cache');
            $this->getResponse()->setHeader('Expires', '0');

        try {
            // Retrieve the current quote
            $quote = $this->checkoutSession->getQuote();
            $cartItems = [];

            // Collect cart items
            foreach ($quote->getAllVisibleItems() as $item) {
                $cartItems[] = [
                    'product' => $item->getProductId(),
                    'qty' => $item->getQty()
                ];
            }

            // Handle empty cart case
            if (empty($cartItems)) {
                throw new \Exception('No items found in the cart to share.');
            }

            // Serialize cart items
            $serializedCartData = $this->jsonSerializer->serialize($cartItems);

            // Generate a unique token
            $token = bin2hex(random_bytes(16));

            // Save the shared cart to the database
            $connection = $this->resourceConnection->getConnection();
            $connection->insert(
                'shared_cart',
                [
                    'token' => $token,
                    'cart_data' => $serializedCartData,
                    'created_at' => (new \DateTime())->format('Y-m-d H:i:s')
                ]
            );

            // Generate the shareable link
            $link = $this->_url->getUrl('sharecart/index/load', ['token' => $token]);

            // Return the success response
            return $result->setData([
                'success' => true,
                'link' => $link
            ]);

        } catch (\Exception $e) {
            // Handle exceptions and return an error response
            return $result->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}
