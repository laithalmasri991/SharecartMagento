<?php

namespace Laith\Magentosharecart\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Checkout\Model\Session;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\ProductFactory;
use Psr\Log\LoggerInterface;

class Load extends Action
{
    protected $resource;
    protected $checkoutSession;
    protected $messageManager;
    protected $productFactory;
    protected $logger;

    public function __construct(
        Context $context,
        ResourceConnection $resource,
        Session $checkoutSession,
        ManagerInterface $messageManager,
        ProductFactory $productFactory,
        LoggerInterface $logger
    ) {
        $this->resource = $resource;
        $this->checkoutSession = $checkoutSession;
        $this->messageManager = $messageManager;
        $this->productFactory = $productFactory;
        $this->logger = $logger;
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
                    $this->logger->error('Invalid cart item: ' . json_encode($item));
                    continue; // Skip invalid items
                }

                $productId = $item['product'];
                $qty = (int) $item['qty'];

                // Load the product and add it to the quote
                $product = $this->productFactory->create()->load($productId);

                if ($product->getId()) {
                    try {
                        $quoteItem = $quote->addProduct($product, $qty);
                        
                        if ($quoteItem) {
                            // Ensure price is correctly set
                            $quoteItem->setCustomPrice($product->getFinalPrice());
                            $quoteItem->setOriginalCustomPrice($product->getFinalPrice());
                            $quoteItem->getProduct()->setIsSuperMode(true);
                        }
                    } catch (\Exception $e) {
                        $this->logger->error(
                            'Failed to add product to cart: ' . $productId . ' - ' . $e->getMessage()
                        );
                        continue;
                    }
                } else {
                    $this->logger->error('Product not found: ' . $productId);
                }
            }

            // Force recalculating totals
            $quote->setTotalsCollectedFlag(false);
            $quote->collectTotals();
            $quote->save();

            // Debug logging
            foreach ($quote->getAllItems() as $quoteItem) {
                $this->logger->debug(
                    'Quote Item: ' . $quoteItem->getProductId() . ' - Price: ' . $quoteItem->getPrice()
                );
            }

            if (!$quote->hasItems()) {
                throw new LocalizedException(__('No valid items were added to the cart.'));
            }

            $this->messageManager->addSuccessMessage(__('Cart has been restored successfully.'));
            return $this->_redirect('checkout/cart');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect('/');
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $this->messageManager->addErrorMessage(__('An error occurred while restoring the cart.'));
            return $this->_redirect('/');
        }
    }
}
