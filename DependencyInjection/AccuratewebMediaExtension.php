<?php
/**
 *  (c) 2019 ИП Рагозин Денис Николаевич. Все права защищены.
 *
 *  Настоящий файл является частью программного продукта, разработанного ИП Рагозиным Денисом Николаевичем
 *  (ОГРНИП 315668300000095, ИНН 660902635476).
 *
 *  Алгоритм и исходные коды программного кода программного продукта являются коммерческой тайной
 *  ИП Рагозина Денис Николаевича. Любое их использование без согласия ИП Рагозина Денис Николаевича рассматривается,
 *  как нарушение его авторских прав.
 *   Ответственность за нарушение авторских прав наступает в соответствии с действующим законодательством РФ.
 */

/**
 * @author Denis N. Ragozin <dragozin@accurateweb.ru>
 */

namespace Accurateweb\MediaBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class AccuratewebMediaExtension extends Extension
{
  /**
   * Loads a specific configuration.
   *
   * @param array $configs An array of configuration values
   * @param ContainerBuilder $container A ContainerBuilder instance
   *
   * @throws \InvalidArgumentException When provided tag is not defined in this extension
   */
  public function load(array $configs, ContainerBuilder $container)
  {
    $loader = new XmlFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
    $loader->load('services.xml');

    $configuration = new Configuration();
    $config = $this->processConfiguration($configuration, $configs);

    $storageProviderMap = [
      'file' => 'aw.media.storage.file',
      'yandex_cloud' => 'aw.media.storage.yandexcloud'
    ];

    $def = $container->getDefinition('aw.media.storage.provider');
    $def->replaceArgument(0, $container->getDefinition($storageProviderMap[$config['media_storage_provider']]));
  }
}
