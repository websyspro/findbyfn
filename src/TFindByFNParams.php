<?php

namespace Websyspro\FindByFn;

use ReflectionClass;
use ReflectionProperty;
use Websyspro\Commons\TList;

class TFindByFNParams
{
  public TList $propertys;

  public function __construct(
    public string $entity,
    public string $name
  ){
    $this->setPropertys();
  }

  private function setPropertys(
  ): void {
    if(class_exists($this->entity) === false){
      $this->propertys = new TList();
    }

    $reflectionClass = (
      new ReflectionClass($this->entity)
    );

    $this->propertys = new TList(
      $reflectionClass->getProperties(
        ReflectionProperty::IS_PUBLIC
      )
    );

    $this->propertys->Mapper(
      fn(ReflectionProperty $rp) => (
        new TFindByFNParamsStructure(
          $rp->getType()->getName(),
          $rp->getName()
        )
      )
    );
  }
}