<?php
/**
 * @author Denis N. Ragozin <dragozin@accurateweb.ru>
 */

namespace Accurateweb\MediaBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
  /**
   * @inheritDoc
   */
  public function getConfigTreeBuilder()
  {
    $treeBuilder = new TreeBuilder();

    $rootNode =
      $treeBuilder
        ->root('accurateweb_media');

    $rootNode
      ->children()
        ->enumNode('media_storage_provider')->values(['file', 'yandex_cloud'])->defaultValue('file')->end()
      ->end()
    ;

    return $treeBuilder;
  }

}
