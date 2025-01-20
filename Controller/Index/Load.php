<?php

namespace Laith\Magentosharecart\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Checkout\Model\Session;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;

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
            // Fetch shared cart data from the database
            $connection = $this->resource->getConnection();
            $cartData = $connection->fetchOne(
                "SELECT cart_data FROM shared_cart WHERE token = :token",
                ['token' => $token]
            );

            if (!$cartData) {
                throw new LocalizedException(__('Invalid or expired cart link.'));
            }

            // Deserialize the cart data
            $cartItems = json_decode($cartData, true);

            if (!is_array($cartItems)) {
                throw new LocalizedException(__('Cart data is corrupted.'));
            }

            $quote = $this->checkoutSession->getQuote();
            $quote->removeAllItems(); // Clear any existing items in the cart

            // Add items to the quote
            foreach ($cartItems as $item) {
                if (empty($item['product']) || empty($item['qty'])) {
                    $this->_objectManager->get('Psr\Log\LoggerInterface')->error('Invalid cart item: ' . json_encode($item));
                    continue; // Skip invalid items
                }

                $productId = $item['product'];
                $qty = $item['qty'];

                // Load the product and add it to the quote
                $product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($productId);

                if ($product->getId()) {
                    try {
                        $quote->addProduct($product, intval($qty));
                    } catch (\Exception $e) {
                        $this->_objectManager->get('Psr\Log\LoggerInterface')->error(
                            'Failed to add product to cart: ' . $productId . ' - ' . $e->getMessage()
                        );
                        continue;
                    }
                } else {
                    $this->_objectManager->get('Psr\Log\LoggerInterface')->error('Product not found: ' . $productId);
                }
            }

            // Collect totals and save the quote
            $quote->collectTotals();
            $quote->save();

            if (!$quote->hasItems()) {
                throw new LocalizedException(__('No valid items were added to the cart.'));
            }

            $this->messageManager->addSuccessMessage(__('Cart has been restored successfully.'));
            return $this->_redirect('checkout/cart');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect('/');
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e->getMessage());
            $this->messageManager->addErrorMessage(__('An error occurred while restoring the cart.'));
            return $this->_redirect('/');
        }
    }
}
