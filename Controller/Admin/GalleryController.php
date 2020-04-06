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

namespace Accurateweb\MediaBundle\Controller\Admin;

use Accurateweb\MediaBundle\Generator\ImageThumbnailGenerator;
use Accurateweb\MediaBundle\Model\Media\ImageInterface;
use Accurateweb\MediaBundle\Model\Media\MediaCroppableInterface;
use Accurateweb\MediaBundle\Model\Thumbnail\ImageThumbnail;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * @author Denis N. Ragozin <dragozin@accurateweb.ru>
 */
class GalleryController extends Controller
{
  public function listAction(Request $request)
  {
    $gallery = $this->get('aw.media.manager')->getGallery($request->get('gallery_provider_id'), $request->get('gallery_id'));

    $medias = $gallery->getMediaObjectManager()->getRepository()->getAll();

    $result = [];

    foreach ($medias as $media)
    {
      $result[] = $this->imageToArray($media);
    }

    return new JsonResponse($result);
  }

  public function deleteAction(Request $request)
  {
    $gallery = $this->get('aw.media.manager')->getGallery($request->get('gallery_provider_id'), $request->get('gallery_id'));

    $mediaObjectManager = $gallery->getMediaObjectManager();

    $media = $mediaObjectManager->getRepository()->find($request->request->get('media_id'));

    if (!$media)
    {
      throw new NotFoundHttpException();
    }

    $storage = $this->get('aw.media.storage.provider')->getMediaStorage($media);

    $storage->remove($media);

    $mediaObjectManager->remove($media);
    $mediaObjectManager->flush();

    return new JsonResponse();
  }

  protected function imageToArray(ImageInterface $media)
  {
    $thumbnail = new ImageThumbnail('preview', $media);

    $mediaStorage = $this->get('aw.media.storage.provider')->getMediaStorage($thumbnail);

    $thumbnailResource = $mediaStorage->retrieve($thumbnail);
    $mediaResource = $mediaStorage->retrieve($media);
    $crop = array(null, null, null, null);

    if ($media instanceof MediaCroppableInterface)
    {
      $mediaCrop = $media->getCrop();
      $crop = array(
        $mediaCrop->getLeft(),
        $mediaCrop->getTop(),
        $mediaCrop->getRight(),
        $mediaCrop->getBottom(),
      );
    }

    return [
      'id' => $media->getId(),
      'crop' => $crop,
      'preview' => array(
        'id' => $thumbnail->getId(),
        'src' => $thumbnailResource ? $thumbnailResource->getUrl() : null
      ),
      'src' => $mediaResource ? $mediaResource->getUrl() : null
    ];
  }
}