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

namespace Accurateweb\MediaBundle\Generator;


use Accurateweb\ImagingBundle\Adapter\AdapterInterface;
use Accurateweb\ImagingBundle\Filter\FilterFactoryInterface;
use Accurateweb\ImagingBundle\Filter\FilterOptionsResolverInterface;
use Accurateweb\MediaBundle\Exception\ThumbnailGeneratorException;
use Accurateweb\MediaBundle\Model\Media\ImageInterface;
use Accurateweb\MediaBundle\Model\Media\Storage\FileMediaStorage;
use Accurateweb\MediaBundle\Model\Media\Storage\MediaStorageInterface;
use Accurateweb\MediaBundle\Model\Thumbnail\ImageThumbnail;
use Accurateweb\MediaBundle\Model\Thumbnail\ThumbnailDefinition;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Генератор миниатюр изображений
 *
 * @package Accurateweb\MediaBundle\Generator
 */
class ImageThumbnailGenerator
{
  private $mediaStorage;

  private $adapter;

  private $filterFactory;

  public function __construct(MediaStorageInterface $mediaStorage, AdapterInterface $adapter,
    FilterFactoryInterface $filterFactory)
  {
    $this->mediaStorage = $mediaStorage;
    $this->adapter = $adapter;
    $this->filterFactory = $filterFactory;
  }

  /**
   * @param ImageInterface $media
   * @param null $id Thumbnail ID
   */
  public function generate(ImageInterface $media, $id=null)
  {
    $thumbnailDefinitions = $media->getThumbnailDefinitions();

    /*
     * Указывает, нужно ли удалить оригинальный файл после обработки. Если используется локальное файловое хранилище,
     * то оригинальный файл удалять не нужно, т.к. для обработки используется сам файл. В других случаях будет создана
     * копия изображения, и ее нужно будет удалить после обработки
     */
    $removeFile = false;

    /*
     * Если изображения хранятся не в локальной файловой системе, то для того, чтобы сгенерировать миниатюры,
     * необходимо сначала скачать копию файла для обработки и сохранить его в локальной файловой системе
     */
    if (!$this->mediaStorage instanceof FileMediaStorage)
    {
      if (!$this->mediaStorage->exists($media))
      {
        throw new FileNotFoundException(sprintf('File %s not found on cloud storage', $media->getResourceId()));
      }

      $mediaResource = $this->mediaStorage->retrieve($media);

      /*
       * Попробуем использовать каталог для временных файлов
       */
      $dir = sys_get_temp_dir();

      $tempFilename = pathinfo($media->getResourceId(), PATHINFO_BASENAME);

      $filename = $dir.DIRECTORY_SEPARATOR.$tempFilename;

      if (false === file_put_contents($filename, $mediaResource->getContent()))
      {
        throw new ThumbnailGeneratorException(sprintf('Unable to save temporary file "%s" for processing', $filename));
      }

      $removeFile = true;
    }
    else
    {
      $filename = $this->mediaStorage->getOriginalFilePath($media);
    }

    if (!file_exists($filename))
    {
      throw new FileNotFoundException(sprintf('File %s not exists', $filename));
    }

    foreach ($thumbnailDefinitions as $definition)
    {
      /** @var $definition ThumbnailDefinition */
      if (null !== $id && $definition->getId() !== $id)
      {
        continue;
      }

      $image = $this->adapter->loadFromFile($filename);
      $filterChain = $definition->getFilterChain();

      foreach ($filterChain as $filterDefinition)
      {
        $options = $filterDefinition['options'];

        if (isset($filterDefinition['resolver']))
        {
          $resolver = $filterDefinition['resolver'];

          if ($resolver instanceof FilterOptionsResolverInterface)
          {
            try
            {
              $options = $resolver->resolve($image, $options);
            } catch (\Exception $e)
            {
              //Filter is not configurable, so we skip it
              continue;
            }
          }
        }

        $filter = $this->filterFactory->create($filterDefinition['id'], $options);
        $filter->process($image);
      }

      $thumbnail = $media->getThumbnail($definition->getId());

      if ($this->mediaStorage instanceof FileMediaStorage)
      {
        $thumbnailPath = $this->mediaStorage->getPublicFilePath($thumbnail);
      }
      else
      {
        $thumbnailPath = $dir . DIRECTORY_SEPARATOR . $thumbnail->getResourceId();
      }

      $thumbnailDir = pathinfo($thumbnailPath, PATHINFO_DIRNAME);

      if (!is_dir($thumbnailDir))
      {
        @mkdir($thumbnailDir, 0777, true);
      }

      $this->adapter->save($image, $thumbnailPath);

      /*
       * Если изображения хранятся не в локальной файловой системе, то нужно выгрузить миниатюру в медиа-хранилище. Если
       * же изображения хранятся в локальной файловой системе, то этого не нужно делать, т.к. можно сохранить файл
       * напрямую в файловую систему в нужный каталог
       */
      if (!$this->mediaStorage instanceof FileMediaStorage)
      {
        try
        {
          $this->mediaStorage->store($thumbnail, new File($thumbnailPath));
        }
        catch (\Exception $e)
        {
          @unlink($thumbnailPath);
          @rmdir($thumbnailDir);
          throw new ThumbnailGeneratorException(sprintf('Unable to store thumbnail in media storage: %s', $e->getMessage()), $e->getCode(), $e);
        }
      }

      unset($image);
    }

    if ($removeFile)
    {
      @unlink($filename);
    }
  }
}
