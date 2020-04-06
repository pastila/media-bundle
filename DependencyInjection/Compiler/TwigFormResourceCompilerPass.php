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

namespace Accurateweb\MediaBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class TwigFormResourceCompilerPass implements CompilerPassInterface
{
  public function process(ContainerBuilder $container)
  {
    if (!$container->hasParameter('twig.form.resources'))
    {
      return;
    }

    $container->setParameter('twig.form.resources', array_merge(
      array('AccuratewebMediaBundle:Form:div_layout.html.twig'),
      $container->getParameter('twig.form.resources')
    ));
  }
}