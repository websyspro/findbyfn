<?php

namespace Websyspro\FindByFn;

class TFindByFN
{
  public TFindByStructure $structure;

  public function __construct(
    public mixed $fn
  ){
    $this->define();
    $this->parse();
    $this->clear();
  }

  public function getConditions(
  ): string {
    return $this->structure->getConditions();
  }

  private function define(
  ): void {
    $this->structure = (
      TFNUtil::getBodyFromFNToFindBy(
        $this->fn
      )
    );
  }

  private function parseEntity(
  ): void {
    $this->structure->params->ForEach(
      fn(TFindByFNParams $params) => (
        $this->structure->finds->Mapper(
          fn(TFindByFNCondition | TFindByFNCompare $findByFN) => (
            $findByFN instanceof TFindByFNCompare ? $findByFN : (
              $findByFN->entity === $params->name 
                ? $findByFN->setEntity($params->entity) 
                : $findByFN 
            )
          )
        )
      )
    );
  }

  private function parseStatics(
  ): void {
    $this->structure->finds->Mapper(
      fn(TFindByFNCondition | TFindByFNCompare $findByFN) => (
        $findByFN instanceof TFindByFNCondition
          ? $findByFN->setStatic($this->structure->statics) 
          : $findByFN
      )
    );
  }

  private function parseValues(
  ): void {
    $this->structure->params->ForEach(
      fn(TFindByFNParams $params) => (
        $this->structure->finds->Mapper(
          fn(TFindByFNCondition | TFindByFNCompare $findByFN) => (
            $findByFN instanceof TFindByFNCompare ? $findByFN : (
              $findByFN->entity === $params->entity 
                ? $findByFN->setValue(
                    $params->propertys->Copy()->Find(
                      fn(TFindByFNParamsStructure $ps) => (
                        $ps->column === $findByFN->column
                      )
                    )->First()
                  ) 
                : $findByFN 
            )
          )
        )
      )
    );
  }

  private function parseJoins(
  ): void {
    $this->structure->params->ForEach(
      fn(TFindByFNParams $params) => (
        $this->structure->finds->Mapper(
          fn(TFindByFNCondition | TFindByFNCompare $findByFN) => (
            $findByFN instanceof TFindByFNCompare ? $findByFN : (
              $findByFN->getEntityByValue() === $params->name 
                ? $findByFN->setEntityByValue( $params ) 
                : $findByFN
            )
          )
        )
      )
    );    
  }

  private function parseCompares(
  ): void {
    $this->structure->finds->ForEach(
      fn(TFindByFNCondition | TFindByFNCompare $findByFN) => (
        $findByFN instanceof TFindByFNCondition
          ? $findByFN->setCompare($this->structure->params->Copy()) 
          : $findByFN
      )
    );
  }

  private function parseScript(
  ): void {
    $this->structure->condition = $this->structure->finds->Count() !== 0 ? (
      $this->structure->finds->Mapper(
        fn(TFindByFNCondition | TFindByFNCompare $findByFN) => (
          $findByFN->get()
        )
      )->JoinWithSpace()
    ) : "1 = 1";
  }

  private function parse(
  ): void {
    $this->parseEntity();
    $this->parseStatics();
    $this->parseValues();
    $this->parseJoins();
    $this->parseCompares();
    $this->parseScript();
  }

  private function clear(
  ): void {
    unset($this->fn);
  }
}