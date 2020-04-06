<?php
/**
 * @author Denis N. Ragozin <dragozin@accurateweb.ru>
 */

namespace Accurateweb\MediaBundle\Model\Media\Storage;

use Accurateweb\MediaBundle\Exception\MediaException;
use Accurateweb\MediaBundle\Exception\NotImplementedException;
use Accurateweb\MediaBundle\Model\Media\MediaInterface;
use Accurateweb\MediaBundle\Model\Media\MediaList\FolderListItem;
use Accurateweb\MediaBundle\Model\Media\MediaList\FileListItem;
use Accurateweb\MediaBundle\Model\Media\MediaList\FileList;
use Accurateweb\MediaBundle\Model\Media\Resource\MediaResourceInterface;
use Accurateweb\MediaBundle\Model\Media\Resource\S3MediaResource;
use Accurateweb\MediaBundle\Model\Media\StorageMedia;
use Aws\Api\Validator;
use Aws\ResultInterface;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Медиа-хранилище Яндекс.Облако
 *
 * @package Accurateweb\MediaBundle\Model\Media\Storage
 */
class YandexCloudMediaStorage extends S3MediaStorage
{
  private $bucket;
  protected $logger;

  public function __construct(S3Client $s3client, $bucket, LoggerInterface $logger)
  {
    parent::__construct($s3client);

    $this->bucket = $bucket;
    $this->logger = $logger;
  }

  /**
   * @param MediaInterface $media
   * @param File $file
   * @throws NotImplementedException
   */
  public function store(MediaInterface $media, File $file)
  {
    $s3client = $this->getS3Client();

    try
    {
      /** @var ResultInterface $result */
      $result = $s3client->upload($this->bucket, $media->getResourceId(), file_get_contents($file), 'public-read');
    }
    catch (S3Exception $e)
    {
      throw new MediaException($e->getMessage(), $e->getCode(), $e);
    }

  }

  /**
   * @param MediaInterface $media
   * @return \Accurateweb\MediaBundle\Model\Media\Resource\MediaResourceInterface|void
   * @throws NotImplementedException
   */
  public function retrieve(MediaInterface $media)
  {
    $s3client = $this->getS3Client();

    try
    {
      /** @var ResultInterface $result */
      $result = $s3client->getObject([
        'Bucket' => $this->bucket,
        'Key' => $media->getResourceId()
      ]);

      return new S3MediaResource($media, $result);
    }
    catch (S3Exception $e)
    {
      throw new MediaException($e->getMessage(), $e->getCode(), $e);
    }
  }

  /**
   * @param MediaInterface $media
   * @return bool|void
   * @throws NotImplementedException
   */
  public function exists(MediaInterface $media)
  {
    $s3client = $this->getS3Client();

    try
    {
      return $s3client->doesObjectExist($this->bucket, $media->getResourceId());
    }
    catch (\Exception $e)
    {
      /**
       * @see Validator::validate()
       */
      $this->logger->error(sprintf('Error in doesObjectExist method. %s', $e->getMessage()));
      return false;
    }
  }

  /**
   * @param MediaInterface $media
   * @return mixed|void
   * @throws NotImplementedException
   */
  public function remove(MediaInterface $media)
  {
    $s3client = $this->getS3Client();

    return $s3client->deleteObject([
      'Bucket' => $this->bucket,
      'Key' => $media->getResourceId()
    ]);
  }

}
