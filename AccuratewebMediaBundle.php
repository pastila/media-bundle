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

namespace Accurateweb\MediaBundle;

use Accurateweb\MediaBundle\DependencyInjection\Compiler\MediaGalleryProviderCompilerPass;
use Accurateweb\MediaBundle\DependencyInjection\Compiler\TwigFormResourceCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AccuratewebMediaBundle extends Bundle
{
  public function build(ContainerBuilder $container)
  {
    $container->addCompilerPass(new MediaGalleryProviderCompilerPass());
    $container->addCompilerPass(new TwigFormResourceCompilerPass());
  }
}