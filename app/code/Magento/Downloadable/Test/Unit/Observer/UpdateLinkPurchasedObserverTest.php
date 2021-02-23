<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Observer;

use Magento\Downloadable\Model\Link\Purchased;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\Collection as PurchasedCollection;
use Magento\Downloadable\Model\ResourceModel\Link\Purchased\CollectionFactory;
use Magento\Downloadable\Observer\UpdateLinkPurchasedObserver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for class Magento\Downloadable\Observer\UpdateLinkPurchasedObserver
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateLinkPurchasedObserverTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $purchasedCollectionFactoryMock;

    /**
     * @var MockObject|Event
     */
    protected $eventMock;

    /**
     * @var MockObject|Observer
     */
    protected $observerMock;

    /**
     * @var MockObject|Order
     */
    private $orderMock;

    /**
     * @var UpdateLinkPurchasedObserver
     */
    protected $updateLinkPurchased;

    /**
     * Setup the fixture.
     */
    protected function setUp(): void
    {
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->purchasedCollectionFactoryMock = $this->getMockBuilder(CollectionFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $this->eventMock = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOrder'])
            ->getMock();
        $this->orderMock = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'getCustomerId'])
            ->getMock();
        $this->observerMock = $this->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEvent'])
            ->getMock();
        $this->updateLinkPurchased = new UpdateLinkPurchasedObserver(
            $this->scopeConfigMock,
            $this->purchasedCollectionFactoryMock
        );
    }

    /**
     * Test UpdateLinkPurchasedObserver when order id and customer id is not null.
     */
    public function testUpdateLinkPurchased(): void
    {
        $item = $this->getMockBuilder(Purchased::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCustomerId', 'save'])
            ->getMock();
        $purchsedLinkCollection = $this->getCollectionMock(
            PurchasedCollection::class,
            [$item],
            ['addFieldToFilter']
        );
        $this->observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(1);

        $this->orderMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(1);

        $this->purchasedCollectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($purchsedLinkCollection);

        $purchsedLinkCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->willReturnSelf();

        $item->expects($this->once())
            ->method('setCustomerId')
            ->with(1)
            ->willReturnSelf();

        $item->expects($this->once())
            ->method('save')
            ->willReturnSelf();
        $this->updateLinkPurchased->execute($this->observerMock);
    }

    /**
     * Test UpdateLinkPurchasedObserver when order id is null.
     */
    public function testUpdateLinkPurchasedMissingOrderId(): void
    {
        $item = $this->getMockBuilder(Purchased::class)
            ->disableOriginalConstructor()
            ->setMethods(['setCustomerId', 'save'])
            ->getMock();
        $purchsedLinkCollection = $this->getCollectionMock(
            PurchasedCollection::class,
            [$item],
            ['addFieldToFilter']
        );
        $this->observerMock->expects($this->once())
            ->method('getEvent')
            ->willReturn($this->eventMock);

        $this->eventMock->expects($this->once())
            ->method('getOrder')
            ->willReturn($this->orderMock);

        $this->orderMock->expects($this->once())
            ->method('getId')
            ->willReturn(null);

        $this->orderMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn(null);

        $this->purchasedCollectionFactoryMock->expects($this->never())
            ->method('create')
            ->willReturn($purchsedLinkCollection);

        $purchsedLinkCollection->expects($this->never())
            ->method('addFieldToFilter')
            ->willReturnSelf();

        $item->expects($this->never())
            ->method('setCustomerId')
            ->with(1)
            ->willReturnSelf();

        $item->expects($this->never())
            ->method('save')
            ->willReturnSelf();
        $this->updateLinkPurchased->execute($this->observerMock);
    }

    /**
     * Get collection mock.
     *
     * @param $className
     * @param $data
     * @param array $methods
     * @return MockObject
     */
    protected function getCollectionMock($className, $data, array $methods = []): MockObject
    {
        $mock = $this->getMockBuilder($className)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->setMethods(array_merge($methods, ['getIterator']))
            ->getMock();
        $iterator = new \ArrayIterator($data);
        $mock->expects(
            $this->any()
        )->method(
            'getIterator'
        )->will(
            $this->returnValue($iterator)
        );
        return $mock;
    }
}
