<?php

namespace Laith\Magentosharecart\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Checkout\Model\Session;
use Magento\Framework\Message\ManagerInterface;

class Load extends Action
{
    protected $resource;
    protected $checkoutSession;
    protected $messageManager;

    public function __construct(
        Context $context,
        ResourceConnection $resource,
        Session $checkoutSession,
        ManagerInterface $messageManager
    ) {
        $this->resource = $resource;
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $token = $this->getRequest()->getParam('token');

        try {
            // Fetch the shared cart data from the database
            $connection = $this->resource->getConnection();
            $cartData = $connection->fetchOne(
                "SELECT cart_data FROM shared_cart WHERE token = :token",
                ['token' => $token]
            );

            if (!$cartData) {
                throw new \Exception('Invalid cart link.');
            }

            // Deserialize the cart data
            $cartItems = json_decode($cartData, true);

            if (!is_array($cartItems)) {
                throw new \Exception('Cart data is invalid or corrupted.');
            }

            $quote = $this->checkoutSession->getQuote();

            // Add items to the quote
            foreach ($cartItems as $item) {
                if (!isset($item['product']) || !isset($item['qty'])) {
                    // Log invalid item data for debugging
                    $this->_objectManager->get('Psr\Log\LoggerInterface')->error(
                        'Invalid cart item: ' . json_encode($item)
                    );
                    continue; // Skip this item if required keys are missing
                }

                $productId = $item['product'];
                $qty = $item['qty'];

                $product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($productId);
                $quote->addProduct($product, intval($qty));
            }

            $quote->save();

            $this->messageManager->addSuccessMessage('Cart has been loaded successfully.');
            return $this->_redirect('checkout/cart');
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect('/');
        }
    }
}