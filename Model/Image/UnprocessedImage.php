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

namespace Accurateweb\MediaBundle\Model\Image;

use Accurateweb\MediaBundle\Model\Image\Image;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnprocessedImage extends Image
{
  public function __construct($id, $resourceId, $options)
  {
    parent::__construct($id, $resourceId, $options);
  }

  protected function configureOptions(OptionsResolver $resolver)
  {
    $resolver->setDefault('crop', []);
  }

  public function getThumbnailDefinitions()
  {
    return array(
    );
  }
}