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

namespace Accurateweb\MediaBundle\Controller;

use Accurateweb\ImagingBundle\Adapter\ImagickAdapter;
use Accurateweb\ImagingBundle\Filter\ImageMagick\ConvertFilter;
use Accurateweb\ImagingBundle\Filter\ImageMagick\ScaleFilter;
use Accurateweb\MediaBundle\Model\Media\MediaInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class MediaController extends Controller
{
  /**
   * @param Request $request
   * @throws \Exception
   */
  public function uploadAction(Request $request)
  {
    $gallery = $this->get('aw.media.manager')->getGallery($request->get('gallery_provider_id'), $request->get('gallery_id'));

    $mediaObjectManager = $gallery->getMediaObjectManager();

    $result = [];
    $statusCode = 200;

    $files = $request->files->all();

    foreach ($files as $id => $file)
    {
      $ext = ($file->guessExtension() && $file->guessExtension() != 'txt')
        ? '.'.$file->guessExtension()
        : ($file->getClientOriginalExtension()?'.'.$file->getClientOriginalExtension():'');

      /** @var $file UploadedFile */
      $media = $gallery->createMedia();

      $resourceId = implode('/', [
        $request->get('gallery_provider_id'),
        $request->get('gallery_id'),
        md5(uniqid()).($ext)
      ]);

      $media->setResourceId($resourceId);

      $mediaObjectManager->persist($media);
      $mediaObjectManager->flush();

      $storage = $this->get('aw.media.storage.provider')->getMediaStorage($media);

      $storage->store($media, $file);

      $this->get('aw_media.thumbnail_generator')->generate($media);

      $result[$id] = $this->mediaToArray($media);
    }

    return new JsonResponse($result, $statusCode);
  }

  public function listAction(Request $request)
  {
    $gallery = $this->get('aw.media.manager')->getGallery($request->get('gallery_provider_id'), $request->get('gallery_id'));

    $medias = $gallery->getMediaObjectManager()->getRepository()->getAll();

    $result = [];

    foreach ($medias as $media)
    {
      $result[] = $this->mediaToArray($media);
    }

    return new JsonResponse($result);
  }

  protected function mediaToArray(MediaInterface $media)
  {
    return [
      'id' => $media->getId(),
      'public_url' => '/uploads/'.$media->getResourceId()
    ];
  }
}