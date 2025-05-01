<?php

namespace Websyspro\FindByFn;

use ReflectionFunction;
use ReflectionParameter;
use Websyspro\Commons\TList;
use Websyspro\Commons\TUtil;

class TFNUtil
{
  private static function reflectFunction(
    callable $fn
  ): ReflectionFunction {
    return new ReflectionFunction($fn);
  }

  public static function getFNLines(
    callable $fn
  ): string {
    $rf = static::reflectFunction($fn);
    $rfLines = new TList(
      array_slice(
        file( $rf->getFileName()),
        bcsub($rf->getStartLine(), 1), $rf->getEndLine() - bcsub($rf->getStartLine(), 1)
      )
    );

    return trim(
      TUtil::JoinNotSpace(
        $rfLines->Mapper(
          fn(string $line) => preg_replace("/\/\/.*$/", "", $line)
        )->All()
      )
    );
  }

  private static function getBodyFromFN(
    callable $fn
  ): string {
    return (
      preg_replace(
        ["/\s{1,}={1}\s{1,}(?!=)/",
         "/\s{1,}={2}\s{1,}(?!=)/",
         "/\s{1,}!=\s{1,}(?!=)/",
         "/\/\*.*?\*\//s",
        ], [" === "," === "," !== ",""], 
        preg_replace(
          ["/(&&|And|and)/", 
           "/(\|\||Or|or)/"
          ], ["And", "Or"], 
          preg_replace(
            ["/(\r?\n)/", 
             "/\s{2,}/",
             "/^.*?\bfn\b/",
             "/^.*?=>\s*/",
             "/(^\(?\s?)|(\)[^;]*;$)|(\)*$)/"
            ], [""," ","fn","",""], (
              is_callable($fn) ? static::getFNLines($fn) : $fn
            )
          )
        )
      )
    );
  }

  private static function getListParameters(
    callable $fn
  ): TList {
    if(is_callable($fn) === false){
      return new TList();
    }

    $reflectionFunction = (
      static::reflectFunction($fn)
    );

    $listParameters = new TList(
      $reflectionFunction->getParameters()
    );

    $listParameters->Mapper(
      fn(ReflectionParameter $rp) => new TFindByFNParams(
        $rp->getType()->getName(), 
        $rp->getName()
      )
    );

    return $listParameters;
  }
  
  private static function getListStatics(
    callable $fn
  ): TList {
    if(is_callable($fn) === false){
      return new TList();
    }

    $reflectionFunction = (
      static::reflectFunction($fn)
    );

    return new TList(
      $reflectionFunction->getStaticVariables()
    );
  }

  private static function getListConditions(
    callable $fn
  ): TList {
    $conditionsCollection = new TList(
      preg_split("/(\s?And\s?)|(\s?Or\s?)/", static::getBodyFromFN($fn), -1, (
        PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY
      ))
    );
      
    $conditionsCollection->Mapper(fn(string $item) => trim($item));
    $conditionsCollection->Mapper(fn(string $item) => (
      (bool)preg_match("/(And)|(Or)/", $item) 
        ? new TFindByFNCompare( $item )
        : new TFindByFNCondition( $item )
    ));

    return $conditionsCollection;
  }

  public static function getBodyFromFNToFindBy(
    callable $fn
  ): TFindByStructure {
    return new TFindByStructure(
      static::getListConditions($fn),
      static::getListParameters($fn),
      static::getListStatics($fn),
      static::getBodyFromFN($fn)
    );
  }
}