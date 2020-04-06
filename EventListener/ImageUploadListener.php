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

namespace Accurateweb\MediaBundle\EventListener;


use Accurateweb\MediaBundle\Annotation\Thumbnail;
use Accurateweb\MediaBundle\Generator\ImageThumbnailGenerator;
use Accurateweb\MediaBundle\Model\Image\ImageAwareInterface;
use Accurateweb\MediaBundle\Model\Media\ImageInterface;
use Accurateweb\MediaBundle\Service\ImageUploader;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PropertyAccess\PropertyAccess;

class ImageUploadListener
{
  private $uploader;

  private $annotationReader;

  private $propertyAccessor;
  private $imageThumbnailGenerator;

  public function __construct(ImageUploader $uploader, AnnotationReader $annotationReader, ImageThumbnailGenerator $imageThumbnailGenerator)
  {
    $this->uploader = $uploader;
    $this->annotationReader = $annotationReader;
    $this->imageThumbnailGenerator = $imageThumbnailGenerator;
    $this->propertyAccessor = PropertyAccess::createPropertyAccessor();

  }

  public function prePersist(LifecycleEventArgs $args)
  {
    $entity = $args->getEntity();

    $this->uploadFile($entity, $args->getObjectManager());
  }

  public function preUpdate(PreUpdateEventArgs $args)
  {
    $entity = $args->getEntity();

    $this->uploadFile($entity, $args->getObjectManager());
  }

  /**
   *
   * @param $object
   * @param ObjectManager $om
   */
  private function uploadFile($object, $om)
  {
    if (!$object instanceof ImageAwareInterface)
    {
      return;
    }

    $meta = $om->getClassMetadata(get_class($object));
    $rc = $meta->getReflectionClass();
    $props = $rc->getProperties();

    foreach ($props as $prop)
    {
      $ann = $this->annotationReader->getPropertyAnnotation($prop, '\\Accurateweb\\MediaBundle\\Annotation\\Image');

      if ($ann)
      {
        $file = $meta->getReflectionProperty($prop->name)->getValue($object);
        $image = $this->propertyAccessor->getValue($object, $prop->name.'_image');

        if (!$image instanceof ImageInterface)
        {
          continue;
        }

        // only upload new files
        if (!$file instanceof UploadedFile)
        {
          continue;
        }

        $ext = $file->getClientOriginalExtension();
        /*
         * MimeTypeGuesser неправильно определяет mimeType для svg
         * FIXME если есть способ понять что это svg как-то лучше, то надо менять
         */
//        if (strpos($file->getMimeType(), 'text') !== false && strpos($file->getClientMimeType(), 'svg') !== false)
//        {
//          $ext = 'svg';
//        }

        $resourceId = implode('/', [
          $image->getId(),
          md5(uniqid()) . ($ext ? '.' . $ext : '')
        ]);

        $image->setResourceId($resourceId);
        $this->uploader->upload($image, $file, mb_strtolower($ext) !== 'svg');
        $this->propertyAccessor->setValue($object, $prop->name.'_image', $image);
      }
    }
  }

}