<?php

namespace Websyspro\FindByFn;

class TFindByFNCompare
{
  public function __construct(
    public string $compare
  ){}

  public function get(
  ): string {
    return $this->compare;
  }
}