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

namespace Accurateweb\MediaBundle\Model\Thumbnail;

use Accurateweb\MediaBundle\Exception\OperationNotSupportedException;
use Accurateweb\MediaBundle\Model\Media\ImageInterface;
use Accurateweb\MediaBundle\Model\Media\MediaInterface;

class ImageThumbnail implements MediaInterface
{
  private $id;

  private $resourceId;

  /**
   * @var ImageInterface
   */
  private $image;

  public function __construct($id, ImageInterface $image)
  {
    $this->id = $id;
    $this->image = $image;
  }

  public function getId()
  {
    return $this->id;
  }

  public function getResourceId()
  {
    if (null === $this->resourceId)
    {
      $imageResourceId = $this->image->getResourceId();

      $extension = pathinfo($imageResourceId, PATHINFO_EXTENSION);

      $this->resourceId = sprintf('%s/%s.%s',
        substr($imageResourceId, 0, -strlen($extension) - 1),
        $this->getId(),
        $extension
      );
    }

    return $this->resourceId;
  }

  public function setResourceId($id)
  {
    throw new OperationNotSupportedException();
  }

}