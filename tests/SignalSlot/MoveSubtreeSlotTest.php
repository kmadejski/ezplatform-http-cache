<?php

/**
 * This file is part of the eZ Publish Kernel package.
 *
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformHttpCacheBundle\Tests\SignalSlot;

use eZ\Publish\Core\SignalSlot\Signal\LocationService\MoveSubtreeSignal;

class MoveSubtreeSlotTest extends AbstractContentSlotTest
{
    protected $locationId = 45;
    protected $parentLocationId = 43;
    protected $oldParentLocationId = 2;

    public function createSignal()
    {
        return new MoveSubtreeSignal(
            [
                'locationId' => $this->locationId,
                'newParentLocationId' => $this->parentLocationId,
                'oldParentLocationId' => $this->oldParentLocationId,
            ]
        );
    }

    public function generateTags()
    {
        $this->tagProviderMock
            ->expects($this->at(0))
            ->method('getTagForPathId')
            ->willReturn('path-' . $this->locationId);

        $this->tagProviderMock
            ->expects($this->at(1))
            ->method('getTagForLocationId')
            ->willReturn('location-' . $this->oldParentLocationId);

        $this->tagProviderMock
            ->expects($this->at(2))
            ->method('getTagForParentId')
            ->willReturn('parent-' . $this->oldParentLocationId);

        $this->tagProviderMock
            ->expects($this->at(3))
            ->method('getTagForLocationId')
            ->willReturn('location-' . $this->parentLocationId);

        $this->tagProviderMock
            ->expects($this->at(4))
            ->method('getTagForParentId')
            ->willReturn('parent-' . $this->parentLocationId);

        return ['path-' . $this->locationId, 'location-' . $this->oldParentLocationId, 'parent-' . $this->oldParentLocationId, 'location-' . $this->parentLocationId, 'parent-' . $this->parentLocationId];
    }

    public function getSlotClass()
    {
        return 'EzSystems\PlatformHttpCacheBundle\SignalSlot\MoveSubtreeSlot';
    }

    public function getReceivedSignalClasses()
    {
        return [MoveSubtreeSignal::class];
    }
}
