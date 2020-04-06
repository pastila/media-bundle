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

namespace Accurateweb\MediaBundle\Twig;


use Accurateweb\MediaBundle\Model\Image\Image;
use Accurateweb\MediaBundle\Model\Image\ImageAwareInterface;
use Accurateweb\MediaBundle\Model\Media\ImageInterface;
use Accurateweb\MediaBundle\Model\Media\Resource\MediaResource;
use Accurateweb\MediaBundle\Model\Media\Resource\WebResourceFactory;
use Accurateweb\MediaBundle\Model\Media\Storage\MediaStorageInterface;
use Accurateweb\MediaBundle\Model\Media\Storage\MediaStorageProvider;
use StoreBundle\Entity\Store\Catalog\Product\Product;

class MediaExtension extends \Twig_Extension
{
  private $mediaStorage;

  public  function __construct(MediaStorageProvider $storageProvider)
  {
    $this->mediaStorage = $storageProvider->getMediaStorage();
  }

  public function getFunctions()
  {
    return array(
      'image_thumbnail_url' => new \Twig_SimpleFunction('image_thumbnail_url', array($this, 'getImageThumbnailUrl')),
      'image_url' => new \Twig_SimpleFunction('image_url', array($this, 'getImageUrl')),
      'image_exists' => new \Twig_SimpleFunction('image_exists', array($this, 'imageExists'))
    );
  }

  /**
   * Выводит миниатюру изображения
   */
  public function getImageThumbnailUrl(ImageAwareInterface $imageAware, $imageId, $thumbnailId)
  {
    $image = $imageAware->getImage($imageId);

    $thumbnail = $image->getThumbnail($thumbnailId);

    $mediaResource = null;
    if ($thumbnail)
    {
      $mediaResource = $this->mediaStorage->retrieve($thumbnail);
    }

    if (!$mediaResource)
    {
      return null;
    }

    return $mediaResource->getUrl();
  }

  public function getImageUrl($imageAware, $imageId=null)
  {
    if ($imageAware instanceof ImageInterface)
    {
      $image = $imageAware;
    }
    else
    {
      if(!$imageAware instanceof ImageAwareInterface)
      {
        return null;
      }

      $image = $imageAware->getImage($imageId);
    }

    $mediaResource = null;

    if ($image)
    {
      $mediaResource = $this->mediaStorage->retrieve($image);
    }

    if (!$mediaResource)
    {
      return null;
    }

    return $mediaResource->getUrl();
  }

  public function imageExists(ImageAwareInterface $imageAware, $imageId=null)
  {
    $image = $imageAware->getImage($imageId);

    return $image && $this->mediaStorage->exists($image);
  }
}
