<?php
/**
 * @author Denis N. Ragozin <dragozin@accurateweb.ru>
 */

namespace Accurateweb\MediaBundle\Model\Media\Resource;


use Accurateweb\MediaBundle\Exception\NotImplementedException;
use Aws\ResultInterface;

class S3MediaResource implements MediaResourceInterface
{
  private $media;

  private $s3result;

  public function __construct($media, ResultInterface $s3result)
  {
    $this->media = $media;
    $this->s3result = $s3result;
  }

  /**
   * @inheritDoc
   */
  public function getUrl()
  {
    return $this->s3result['@metadata']['effectiveUri'];
  }

  /**
   * @inheritDoc
   */
  public function getContent()
  {
    return $this->s3result['Body'];
  }


}
