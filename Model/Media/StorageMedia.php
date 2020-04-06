<?php

namespace Accurateweb\MediaBundle\Model\Media;

use Accurateweb\MediaBundle\Model\Media\MediaInterface;

class StorageMedia implements MediaInterface
{
  protected $id;
  protected $resourceId;

  public function __construct ($id, $resourceId)
  {
    $this->id = $id;
    $this->resourceId = $resourceId;
  }

  public function getId ()
  {
    return $this->id;
  }

  public function getResourceId ()
  {
    return $this->resourceId;
  }

  public function setResourceId ($id)
  {
    $this->resourceId = $id;
  }
}