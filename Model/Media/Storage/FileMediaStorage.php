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

namespace Accurateweb\MediaBundle\Model\Media\Storage;


use Accurateweb\MediaBundle\Model\Media\MediaInterface;
use Accurateweb\MediaBundle\Model\Media\Resource\MediaResource;
use Accurateweb\MediaBundle\Model\Media\Resource\MediaResourceInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileException; //добавил

class FileMediaStorage implements MediaStorageInterface
{
  /**
   * Каталог сохранения оригинальных файлов
   *
   * @var string
   */
  private $originalsDir;

  /**
   * Каталог сохранения файлов, доступных для загрузки
   *
   * @var string
   */
  private $uploadsDir;

  /**
   * @var string
   */
  private $urlPrefix;

  public function __construct($uploadsDir, $originalsDir=null, $urlPrefix='')
  {
    $this->uploadsDir = $uploadsDir;
    $this->originalsDir = $originalsDir;
    $this->urlPrefix = $urlPrefix;
  }


  public function store(MediaInterface $media, File $file)
  {
    $this->copy($media, $this->originalsDir, $file);
    $this->copy($media, $this->uploadsDir, $file);
  }

  /**
   * @param MediaInterface $media
   * @return MediaResourceInterface
   */
  public function retrieve(MediaInterface $media)
  {
    $resource = new MediaResource($media, $this->uploadsDir, $this->urlPrefix);

    if (!$resource->fileExists())
    {
      $resource = null;
    }

    return $resource;
  }

  public function getOriginalFilePath(MediaInterface $media)
  {
    return $this->originalsDir.'/'.$media->getResourceId();
  }

  public function getPublicFilePath(MediaInterface $media)
  {
    return $this->uploadsDir.'/'.$media->getResourceId();
  }

  public function copy(MediaInterface $media, $baseDir, File $file)
  {
    if (!realpath($baseDir))
    {
      if (!@mkdir($baseDir, 0777, true))
      {
        throw new FileException(sprintf('Unable to create the "%s" directory', $baseDir));
      }
    }

    $baseDir = realpath($baseDir);
    $path = $baseDir.'/'.$media->getResourceId();

    $directory = pathinfo($path, PATHINFO_DIRNAME);

    if (!is_dir($directory))
    {
      if (false === @mkdir($directory, 0777, true) && !is_dir($directory))
      {
        throw new FileException(sprintf('Unable to create the "%s" directory', $directory));
      }
    }
    elseif (!is_writable($directory))
    {
      throw new FileException(sprintf('Unable to write in the "%s" directory', $directory));
    }

    if (!@copy($file->getPathname(), $path))
    {
      $error = error_get_last();
      throw new FileException(sprintf('Could not move the file "%s" to "%s" (%s)', $file->getPathname(), $path, strip_tags($error['message'])));
    }
  }

  public function exists(MediaInterface $media)
  {
    return null !== $this->retrieve($media);
  }

  public function remove(MediaInterface $media)
  {
    @unlink($this->getOriginalFilePath($media));
    @unlink($this->getPublicFilePath($media));
  }
}