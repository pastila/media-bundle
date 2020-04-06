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

namespace Accurateweb\MediaBundle\Model\Image;


use Accurateweb\MediaBundle\Model\Media\ImageInterface;
use Accurateweb\MediaBundle\Model\Thumbnail\ImageThumbnail;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class Image implements ImageInterface
{
  /**
   * @var string
   */
  private $resourceId;

  /**
   * @var string
   */
  private $id;

  function __construct($id, $resourceId, $options=array())
  {
    $this->id = $id;
    $this->resourceId = $resourceId;
    $resolver = new OptionsResolver();
    $this->configureOptions($resolver);
    $this->options = $resolver->resolve($options);
  }

  /**
   * @return integer
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @return string
   */
  public function getResourceId()
  {
    return $this->resourceId;
  }

  /**
   * @param $id string
   */
  public function setResourceId($id)
  {
    $this->resourceId = $id;
  }

  /**
   * Returns thumbnail for an image
   *
   * @param $id string
   * @return ImageThumbnail
   * @throws \Exception
   */
  public function getThumbnail($id)
  {
    $definitions = $this->getThumbnailDefinitions();

    if (!isset($definitions[$id]))
    {
      throw new \Exception(sprintf('Thumbnail "%s" is not defined', $id));
    }

    return new ImageThumbnail($id, $this);
  }

  protected function configureOptions(OptionsResolver $resolver)
  {

  }

}