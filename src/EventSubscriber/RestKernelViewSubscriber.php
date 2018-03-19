<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */
namespace EzSystems\PlatformHttpCacheBundle\EventSubscriber;

use eZ\Publish\API\Repository\Values\Content\Section;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\REST\Server\Values\CachedValue;
use eZ\Publish\Core\REST\Server\Values\ContentTypeGroupList;
use eZ\Publish\Core\REST\Server\Values\ContentTypeGroupRefList;
use eZ\Publish\Core\REST\Server\Values\RestContentType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use EzSystems\PlatformHttpCacheBundle\Handler\TagHandlerInterface;

/**
 * Set cache tags on a few REST responses used by UI in order to be able to cache them.
 */
class RestKernelViewSubscriber implements EventSubscriberInterface
{

    /** @var \EzSystems\PlatformHttpCacheBundle\Handler\TagHandlerInterface */
    private $tagHandler;

    public function __construct(TagHandlerInterface $tagHandler)
    {
        $this->tagHandler = $tagHandler;
    }

    public static function getSubscribedEvents()
    {
        return [KernelEvents::VIEW => ['tagRestResult', 10]];
    }

    public function tagRestResult(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        if (!$request->isMethodCacheable() || !$request->attributes->get('is_rest_request')) {
            return;
        }

        $tags = [];
        $restValue = $event->getControllerResult();

        if ($restValue instanceof ContentTypeGroupList || $restValue instanceof ContentTypeGroupRefList) {
            foreach ($restValue->contentTypeGroups as $contentTypeGroup) {
                $tags[] = 'type-group-' . $contentTypeGroup->id;
            }
        }

        if ($restValue instanceof ContentTypeGroupRefList || $restValue instanceof RestContentType) {
            $tags[] = 'type-' . $restValue->contentType->id;
        }

        if ($restValue instanceof ContentType) {
            $tags[] = 'type-' . $restValue->id;
        }

        if ($restValue instanceof Section) {
            $tags[] = 'section-' . $restValue->id;
        }

        if (empty($tags)) {
            return;
        }

        $this->tagHandler->addTags($tags);
        $event->setControllerResult(new CachedValue($restValue));
    }
}
