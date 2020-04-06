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

namespace Accurateweb\MediaBundle\Command;

use Accurateweb\ImagingBundle\Adapter\GdImageAdapter;
use Accurateweb\ImagingBundle\Filter\GdFilterFactory;
use Accurateweb\MediaBundle\Generator\ImageThumbnailGenerator;
use Accurateweb\MediaBundle\Model\Image\ImageAwareInterface;
use Accurateweb\MediaBundle\Model\Media\ImageInterface;
use StoreBundle\Entity\Store\Catalog\Product\ProductImage;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class GenerateThumbnailsCommand extends ContainerAwareCommand
{
  protected function configure()
  {
    $this
      ->setName('media:thumbnails:generate')
      ->setDescription('Generate all registered thumbnails for images')
      ->setHelp('Generates all registered thumbnails for images')
      ->addArgument('entity', null, 'Entity');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $io = new SymfonyStyle($input, $output);
    $iterator = $this
      ->getContainer()
      ->get('doctrine.orm.entity_manager')
      ->getRepository($input->getArgument('entity'))
      ->createQueryBuilder('m')
      ->getQuery()
      ->iterate()
    ;

    $mediaStorageProvider = $this->getContainer()->get('aw.media.storage.provider');
    $generator = new ImageThumbnailGenerator(
      $mediaStorageProvider->getMediaStorage(null),
      $this->getContainer()->get('aw_imaging.adapter'),
      $this->getContainer()->get('aw_imaging.filter.factory')
    );

    foreach ($iterator as $media)
    {
      $image = $media[0];

      if ($image instanceof ImageAwareInterface)
      {
        $image = $image->getImage();
      }

      if (!$image instanceof ImageInterface)
      {
        continue;
      }

      try
      {
        $generator->generate($image);
        $io->note(sprintf('generate %s', $image->getResourceId()));
      }
      catch (FileNotFoundException $e)
      {
        $io->error($e->getMessage());
//        $output->writeln($e->getMessage());
      }

    }
  }
}
