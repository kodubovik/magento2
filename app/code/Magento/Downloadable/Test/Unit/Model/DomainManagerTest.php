<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Downloadable\Test\Unit\Model;

use Magento\Downloadable\Model\DomainManager;
use Magento\Framework\App\DeploymentConfig\Writer as ConfigWriter;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\File\ConfigFilePool;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Tests for class Magento\Downloadable\Model\DomainManager
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DomainManagerTest extends TestCase
{
    /**
     * @var ConfigWriter|MockObject
     */
    protected $configWriter;

    /**
     * @var DeploymentConfig|MockObject
     */
    protected $deploymentConfig;

    /**
     * @var DomainManager
     */
    protected $domainManager;

    /**
     * Setup the fixture.
     */
    public function setup(): void
    {
        $this->configWriter = $this->getMockBuilder(ConfigWriter::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['saveConfig'])
            ->getMock();
        $this->deploymentConfig = $this->getMockBuilder(DeploymentConfig::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();

        $this->domainManager = new DomainManager(
            $this->configWriter,
            $this->deploymentConfig
        );
    }

    /**
     * Test getDomainsMethod.
     */
    public function testGetDomains(): void
    {
        $downloadableDomainsConfig = [
            'www.TEST.com',
            'www.some-other-domain.com'
        ];
        $downloadableDomainsResult = [
            'www.test.com',
            'www.some-other-domain.com'
        ];

        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with('downloadable_domains')
            ->willReturn($downloadableDomainsConfig);
        $this->assertEquals($downloadableDomainsResult, $this->domainManager->getDomains());
    }

    /**
     * Test addDomains method.
     */
    public function testAddDomains(): void
    {
        $downloadableDomainsConfig = [
            'www.TEST.com',
            'www.some-other-domain.com'
        ];
        $newDomains = ['www.new-domain.com', 'www.TEST.com'];
        $saveDomains = [
            'www.test.com',
            'www.some-other-domain.com',
            'www.new-domain.com'
        ];
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with('downloadable_domains')
            ->willReturn($downloadableDomainsConfig);
        $this->configWriter->expects($this->once())
            ->method('saveConfig')
            ->with(
                [
                    ConfigFilePool::APP_ENV => [
                        'downloadable_domains' => $saveDomains
                    ]
                ],
                true
            );
        $this->domainManager->addDomains($newDomains);
    }

    /**
     * Test removeDomains method.
     */
    public function testRemoveDomains(): void
    {
        $downloadableDomainsConfig = [
            'www.TEST.com',
            'www.some-other-domain.com'
        ];
        $removeDomains = ['www.some-other-domain.com'];
        $saveDomains = [
            'www.test.com'
        ];
        $this->deploymentConfig->expects($this->once())
            ->method('get')
            ->with('downloadable_domains')
            ->willReturn($downloadableDomainsConfig);
        $this->configWriter->expects($this->once())
            ->method('saveConfig')
            ->with(
                [
                    ConfigFilePool::APP_ENV => [
                        'downloadable_domains' => $saveDomains
                    ]
                ],
                true
            );
        $this->domainManager->removeDomains($removeDomains);
    }
}
