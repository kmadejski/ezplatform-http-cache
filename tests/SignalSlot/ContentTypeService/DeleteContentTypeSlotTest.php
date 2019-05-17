<?php

/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformHttpCacheBundle\Tests\SignalSlot\ContentTypeService;

use eZ\Publish\Core\SignalSlot\Signal\ContentTypeService\DeleteContentTypeSignal;
use EzSystems\PlatformHttpCacheBundle\SignalSlot\ContentTypeService\DeleteContentTypeSlot;
use EzSystems\PlatformHttpCacheBundle\Tests\SignalSlot\AbstractSlotTest;

class DeleteContentTypeSlotTest extends AbstractSlotTest
{
    public function createSignal()
    {
        return new DeleteContentTypeSignal([
            'contentTypeId' => 4,
        ]);
    }

    public function generateTags()
    {
        $this->tagProviderMock
            ->expects($this->at(0))
            ->method('getTagForContentTypeId')
            ->with(4)
            ->willReturn('content-type-4');

        $this->tagProviderMock
            ->expects($this->at(1))
            ->method('getTagForTypeId')
            ->with(4)
            ->willReturn('type-4');

        return ['content-type-4', 'type-4'];
    }

    public function getReceivedSignalClasses()
    {
        return [
            DeleteContentTypeSignal::class,
        ];
    }

    public function getSlotClass()
    {
        return DeleteContentTypeSlot::class;
    }
}
