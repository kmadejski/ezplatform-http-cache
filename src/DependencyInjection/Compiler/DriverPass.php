<?php

namespace EzSystems\PlatformHttpCacheBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * We support http cache drivers to be provided by 3rd party bundles.
 * This pass loads those drivers as documented in doc/drivers.md.
 */
class DriverPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->removeAlias('ezpublish.http_cache.purge_client');

        $purgeType = $container->getParameter('ezpublish.http_cache.purge_type');
        $configuredPurgeClientServiceId = static::getTaggedService($container, 'ezplatform.http_cache.purge_client');
        if ($configuredPurgeClientServiceId === null) {
            throw new \InvalidArgumentException("No driver found being able to handle purge_type '$purgeType'.");
        }
        $container->setAlias('ezplatform.http_cache.purge_client', $configuredPurgeClientServiceId);

        // TagHandler is responsible for setting correct tags (recognized by the http cache) on responses
        $configuredTagHandlerServiceId = static::getTaggedService($container, 'ezplatform.http_cache.tag_handler');
        if ($configuredTagHandlerServiceId !== null) {
            $container->setAlias('ezplatform.http_cache.tag_handler', $configuredTagHandlerServiceId);
        }

        // FOS TagHandler is making sure running "php app/console fos:httpcache:invalidate:tag <tag>" works
        $configuredFosTagHandlerServiceId = static::getTaggedService($container, 'ezplatform.http_cache.fos_tag_handler');
        if ($configuredFosTagHandlerServiceId === null) {
            // We default to xkey handler. This one should anyway work for most drivers as it just passes a purge request
            // on to the purge client
            $configuredFosTagHandlerServiceId = 'ezplatform.http_cache.tag_handler.xkey';
        }

        // Re-define fos tag handler to our own so we can make sure tag handling is consitent using same instance
        $container->setAlias('fos_http_cache.handler.tag_handler', $configuredFosTagHandlerServiceId);
    }

    public static function getTaggedService(ContainerBuilder $container, $tag)
    {
        $purgeType = $container->getParameter('ezpublish.http_cache.purge_type');
        $configuredTagHandlerServiceId = null;

        $tagHandlerServiceIds = $container->findTaggedServiceIds($tag);
        foreach ($tagHandlerServiceIds as $tagHandlerServiceId => $attributes) {
            $currentPurgeTypeId = null;
            $currentTagHandlerServiceId = null;
            foreach ($attributes as $attribute) {
                if (array_key_exists('purge_type', $attribute)) {
                    $currentPurgeTypeId = $attribute['purge_type'];
                }
                if ($currentPurgeTypeId !== null) {
                    if ($purgeType === $attribute['purge_type']) {
                        $configuredTagHandlerServiceId = $tagHandlerServiceId;
                        break 2;
                    }
                }
            }
            if ($currentPurgeTypeId === null) {
                throw new \InvalidArgumentException("Missing attribute 'purge_type' in tagged service '$tagHandlerServiceId'.");
            }
        }

        return $configuredTagHandlerServiceId;
    }
}
