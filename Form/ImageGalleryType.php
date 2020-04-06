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

namespace Accurateweb\MediaBundle\Form;

use Accurateweb\MediaBundle\Model\Media\MediaManager;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Router;

class ImageGalleryType extends AbstractType
{
  private $router;

  private $mediaManager;

  public function __construct(Router $router, MediaManager $mediaManager)
  {
    $this->router = $router;
    $this->mediaManager = $mediaManager;
  }

  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    parent::buildForm($builder, $options);
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver
      ->setDefault('mapped', false)
      ->setRequired('gallery')
      ->setDefault('crop', false)

      ->setAllowedValues('mapped', array(false))
    ;
  }

  public function buildView(FormView $view, FormInterface $form, array $options)
  {
    $entity = $form->getParent()->getData();

    $galleryId = $entity->getId();

    $view->vars = array_replace($view->vars, array(
      'image_list_url' => $this->router->generate('accurateweb_media_bundle_image_gallery_editor_list', array(
        'gallery_provider_id' => $options['gallery'],
        'gallery_id' => $galleryId
      )),
      'image_upload_url' => $this->router->generate('accurateweb_media_bundle_gallery_upload', array(
        'gallery_provider_id' => $options['gallery'],
        'gallery_id' => $galleryId
      )),
      'image_delete_url' => $this->router->generate('accurateweb_media_bundle_image_gallery_editor_delete', array(
        'gallery_provider_id' => $options['gallery'],
        'gallery_id' => $galleryId
      )),
      'crop' => $this->getCropOptions($options),
      'js_options' => array()
    ));
  }

  public function getBlockPrefix()
  {
    return "aw_media_gallery";
  }

  protected function getCropOptions($options)
  {
    $cropOptions = $options['crop'];
    $crop = null;

    if (false !== $cropOptions)
    {
      $crop = array();

      if (!isset($cropOptions['size']))
      {
        throw new \InvalidArgumentException('Crop option requires size parameter to be set');
      }

      if (isset($cropOptions['boxWidth']))
      {
        $crop['boxWidth'] = $cropOptions['boxWidth'];
      }

      if (isset($cropOptions['boxHeight']))
      {
        $crop['boxHeight'] = $cropOptions['boxHeight'];
      }

      $size = $cropOptions['size'];

      if (is_string($size))
      {
        $size = explode('x', $size);
      }

      $crop['aspectRatio'] = $size[1] > 0 ? round($size[0] / $size[1], 2) : 1;
      $crop['minSize'] = $size;
    }

    return $crop;
  }
}