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

namespace Accurateweb\MediaBundle\Model\Gallery;

use Accurateweb\MediaBundle\Model\Media\MediaFactoryInterface;
use Accurateweb\MediaBundle\Model\Gallery\MediaGalleryInterface;

class MediaGallery implements MediaGalleryInterface
{
  private $id;

  private $name;

  private $mediaObjectManager;

  private $mediaFactory;

  public function __construct($id, $name, MediaObjectManager $mediaObjectManager, MediaFactoryInterface $mediaFactory)
  {
    $this->id = $id;
    $this->name = $name;
    $this->mediaObjectManager = $mediaObjectManager;
    $this->mediaFactory = $mediaFactory;
  }

  /**
   * @return mixed
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * @return mixed
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * @return MediaObjectManager
   */
  public function getMediaObjectManager()
  {
    return $this->mediaObjectManager;
  }

  /**
   * @return \Accurateweb\MediaBundle\Model\Media\MediaInterface
   */
  public function createMedia()
  {
    return $this->mediaFactory->create($this->id);
  }
}