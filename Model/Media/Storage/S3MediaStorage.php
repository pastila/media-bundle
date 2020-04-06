<?php
/**
 * @author Denis N. Ragozin <dragozin@accurateweb.ru>
 */

namespace Accurateweb\MediaBundle\Model\Media\Storage;

use Aws\S3\S3Client;

/**
 * Совместимое с Amazon S3 медиа-хранилище
 *
 * @package Accurateweb\MediaBundle\Model\Media\Storage
 */
abstract class S3MediaStorage implements MediaStorageInterface
{
  /**
   * @var S3Client
   */
  private $s3client;

  public function __construct(S3Client $s3client)
  {
    $this->s3client = $s3client;
  }

  protected function getS3Client()
  {
    return $this->s3client;
  }
}
