<?php declare(strict_types=1);

namespace Bold\OrderComment\Block\Order\Comment;

use Bold\OrderComment\Model\Data\OrderComment;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Escaper;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

class ViewModel implements ArgumentInterface
{
    public function __construct(
        private readonly RequestInterface        $request,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly Escaper                  $escaper,
        private readonly LoggerInterface          $logger
    ) {}

    /**
     * Get the current order from the request.
     * The order view controller has already validated customer ownership
     * before this block renders, so no additional auth check is needed.
     */
    public function getOrder(): ?OrderInterface
    {
        $orderId = (int) $this->request->getParam('order_id');
        if (!$orderId) {
            return null;
        }

        try {
            return $this->orderRepository->get($orderId);
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf('[OrderComment] Failed to load order %d: %s', $orderId, $e->getMessage())
            );
            return null;
        }
    }

    public function getOrderComment(): string
    {
        $order = $this->getOrder();
        if ($order === null) {
            return '';
        }

        return trim((string) $order->getData(OrderComment::COMMENT_FIELD_NAME));
    }

    public function hasOrderComment(): bool
    {
        return strlen($this->getOrderComment()) > 0;
    }

    public function getOrderCommentHtml(): string
    {
        return nl2br($this->escaper->escapeHtml($this->getOrderComment()));
    }
}