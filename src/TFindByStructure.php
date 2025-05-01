<?php

namespace Websyspro\FindByFn;

use Websyspro\Commons\TList;

class TFindByStructure
{
  public string $condition;

  public function __construct(
    public TList $finds,
    public TList $params,
    public TList $statics,
    public string $body
  ){}

  public function getConditions(
  ): string {
    return $this->condition;
  }
}