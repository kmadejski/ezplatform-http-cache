<?php

/**
 * File containing the RepositoryPrefixDecoratorTest class.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformHttpCacheBundle\Tests\PurgeClient;

use EzSystems\PlatformHttpCacheBundle\PurgeClient\PurgeClientInterface;
use EzSystems\PlatformHttpCacheBundle\PurgeClient\RepositoryPrefixDecorator;
use EzSystems\PlatformHttpCacheBundle\RepositoryTagPrefix;
use EzSystems\PlatformHttpCacheBundle\TagProvider\TagProviderInterface;
use PHPUnit\Framework\TestCase;

class RepositoryPrefixDecoratorTest extends TestCase
{
    /**
     * @var PurgeClientInterface
     */
    private $purgeClientMock;

    /**
     * @var RepositoryTagPrefix
     */
    private $tagPrefixMock;

    /**
     * @var RepositoryPrefixDecorator
     */
    private $prefixDecorator;

    /**
     * @var TagProviderInterface
     */
    private $tagProviderMock;

    protected function setUp()
    {
        parent::setUp();

        $this->purgeClientMock = $this->createMock(PurgeClientInterface::class);
        $this->tagPrefixMock = $this->createMock(RepositoryTagPrefix::class);
        $this->tagProviderMock = $this->createMock(TagProviderInterface::class);
        $this->prefixDecorator = new RepositoryPrefixDecorator($this->purgeClientMock, $this->tagPrefixMock, $this->tagProviderMock);
    }

    protected function tearDown()
    {
        unset($this->purgeClientMock, $this->tagPrefixMock, $this->prefixDecorator, $this->tagProviderMock);

        parent::tearDown();
    }

    public function testPurge()
    {
        $this->purgeClientMock
            ->expects($this->once())
            ->method('purge')
            ->with($this->equalTo(['location-123', 'content-44', 'ez-all']));

        $this->tagPrefixMock
            ->expects($this->once())
            ->method('getRepositoryPrefix')
            ->willReturn('');

        $this->tagProviderMock
            ->expects($this->once())
            ->method('getTagForLocationId')
            ->with(123)
            ->willReturn('location-123');

        $this->prefixDecorator->purge([123, 'content-44', 'ez-all']);
    }

    public function testPurgeWithPrefix()
    {
        $this->purgeClientMock
            ->expects($this->once())
            ->method('purge')
            ->with($this->equalTo(['intranet_location-123', 'intranet_content-44', 'intranet_ez-all']));

        $this->tagPrefixMock
            ->expects($this->once())
            ->method('getRepositoryPrefix')
            ->willReturn('intranet_');

        $this->tagProviderMock
            ->expects($this->once())
            ->method('getTagForLocationId')
            ->with(123)
            ->willReturn('location-123');

        $this->prefixDecorator->purge([123, 'content-44', 'ez-all']);
    }

    public function testPurgeAll()
    {
        $this->purgeClientMock
            ->expects($this->once())
            ->method('purgeAll');

        $this->tagPrefixMock
            ->expects($this->never())
            ->method('getRepositoryPrefix');

        $this->prefixDecorator->purgeAll();
    }
}
