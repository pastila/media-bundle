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

namespace Accurateweb\MediaBundle\Model\Media\Resource;

use Accurateweb\MediaBundle\Model\Media\MediaInterface;

class MediaResource implements MediaResourceInterface
{
  private $pathPrefix;

  private $urlPrefix;

  private $media;

  public function __construct(MediaInterface $media, $pathPrefix, $urlPrefix)
  {
    $this->media = $media;
    $this->pathPrefix = $pathPrefix;
    $this->urlPrefix = $urlPrefix;
  }

  public function getUrl()
  {
    return sprintf('%s/%s', $this->urlPrefix, $this->media->getResourceId());
  }

  public function getPath()
  {
    return sprintf('%s/%s', $this->pathPrefix, $this->fixResourceId($this->media->getResourceId()));
  }

  public function fileExists()
  {
    return is_file($this->getPath());
  }

  public function fixResourceId($resourceId)
  {
    return preg_replace('/^([\/\\\\]+)/', '', $resourceId);
  }

  /**
   * @inheritDoc
   */
  public function getContent()
  {
    return file_get_contents($this->getPath());
  }


}
