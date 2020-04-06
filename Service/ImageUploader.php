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

namespace Accurateweb\MediaBundle\Service;

use Accurateweb\ImagingBundle\Adapter\AdapterInterface;
use Accurateweb\ImagingBundle\Adapter\GdImageAdapter;
use Accurateweb\ImagingBundle\Filter\FilterFactoryInterface;
use Accurateweb\ImagingBundle\Filter\GdFilterFactory;
use Accurateweb\MediaBundle\Generator\ImageThumbnailGenerator;
use Accurateweb\MediaBundle\Model\Media\ImageInterface;
use Accurateweb\MediaBundle\Model\Media\MediaInterface;
use Accurateweb\MediaBundle\Model\Media\MediaManager;
use Accurateweb\MediaBundle\Model\Media\Storage\MediaStorageProvider;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @author Denis N. Ragozin <dragozin@accurateweb.ru>
 */
class ImageUploader
{
  private $mediaStorageProvider;
  private $adapter;
  private $filterFactory;

  public function __construct(MediaStorageProvider $mediaStorageProvider, AdapterInterface $adapter,
    FilterFactoryInterface $filterFactory)
  {
    $this->mediaStorageProvider = $mediaStorageProvider;
    $this->adapter = $adapter;
    $this->filterFactory = $filterFactory;
  }

  public function upload(ImageInterface $image, UploadedFile $file, $generateThumbnails=true)
  {
    $storage = $this->mediaStorageProvider->getMediaStorage($image);

    $storage->store($image, $file);

    if ($generateThumbnails)
    {
      $generator = new ImageThumbnailGenerator($this->mediaStorageProvider->getMediaStorage($image),
        $this->adapter, $this->filterFactory);

      $generator->generate($image);
    }
  }
}
