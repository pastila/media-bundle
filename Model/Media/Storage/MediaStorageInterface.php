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
use Accurateweb\MediaBundle\Model\Media\Resource\MediaResourceInterface;
use Symfony\Component\HttpFoundation\File\File;

interface MediaStorageInterface
{
  public function store(MediaInterface $media, File $file);

  /**
   * Retrieve media from storage
   *
   * @param MediaInterface $media
   * @return MediaResourceInterface
   */
  public function retrieve(MediaInterface $media);

  /**
   * Returns true is a media file exists in the storage
   *
   * @param MediaInterface $media
   * @return bool
   */
  public function exists(MediaInterface $media);

  /**
   * Remove media from storage
   *
   * @param MediaInterface $media
   * @return mixed
   */
  public function remove(MediaInterface $media);
}