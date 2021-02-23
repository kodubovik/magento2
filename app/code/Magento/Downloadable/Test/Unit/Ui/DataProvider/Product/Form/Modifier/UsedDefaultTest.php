<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Product;
use Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\UsedDefault;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for class Magento\Downloadable\Ui\DataProvider\Product\Form\Modifier\UsedDefault
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UsedDefaultTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    protected $objectManagerHelper;

    /**
     * @var LocatorInterface|MockObject
     */
    protected $locatorMock;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var ArrayManager|MockObject
     */
    protected $arrayManagerMock;

    /**
     * @var Product|MockObject
     */
    protected $productMock;

    /**
     * @var UsedDefault
     */
    protected $usedDefault;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->locatorMock = $this->getMockForAbstractClass(LocatorInterface::class);
        $this->scopeConfigMock = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->productMock = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStoreId', 'getAttributeDefaultValue'])
            ->getMock();
        $this->arrayManagerMock = $this->createMock(ArrayManager::class);
        $this->usedDefault = $this->objectManagerHelper->getObject(
            UsedDefault::class,
            [
                'locator' => $this->locatorMock,
                'scopeConfig' => $this->scopeConfigMock,
                'arrayManager' => $this->arrayManagerMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testModifyMetaDefaultStore(): void
    {
        $this->locatorMock->expects($this->exactly(7))
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->productMock->expects($this->exactly(5))
            ->method('getStoreId')
            ->willReturn(0);
        $this->productMock->expects($this->exactly(2))
            ->method('getAttributeDefaultValue')
            ->withConsecutive(['links_title'], ['samples_title'])
            ->willReturnOnConsecutiveCalls(true, true);
        $this->arrayManagerMock->expects($this->never())
            ->method('findPath')
            ->willReturn(null);
        $this->arrayManagerMock->expects($this->never())
            ->method('merge')
            ->willReturn([]);
        $this->scopeConfigMock
            ->method('getValue')
            ->with(
                \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )->willReturn(\Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE);

        $this->assertEquals([], $this->usedDefault->modifyMeta([]));
    }

    /**
     * @return void
     */
    public function testModifyMetaNotDefaultStore(): void
    {
        $this->locatorMock->expects($this->exactly(7))
            ->method('getProduct')
            ->willReturn($this->productMock);
        $this->productMock->expects($this->exactly(5))
            ->method('getStoreId')
            ->willReturn(1);
        $this->productMock->expects($this->exactly(2))
            ->method('getAttributeDefaultValue')
            ->withConsecutive(['links_title'], ['samples_title'])
            ->willReturnOnConsecutiveCalls(true, true);
        $this->arrayManagerMock->expects($this->exactly(5))
            ->method('findPath')
            ->willReturn(null);
        $this->arrayManagerMock->expects($this->exactly(2))
            ->method('merge')
            ->willReturn([]);
        $this->scopeConfigMock
            ->method('getValue')
            ->with(
                \Magento\Store\Model\Store::XML_PATH_PRICE_SCOPE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )->willReturn(\Magento\Store\Model\Store::PRICE_SCOPE_WEBSITE);
        $this->arrayManagerMock->expects($this->exactly(3))
            ->method('set')
            ->willReturn([]);

        $this->assertEquals([], $this->usedDefault->modifyMeta([]));
    }
}
