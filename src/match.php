<?php declare(strict_types=1);

namespace Phunkie\Lang\PatternMatching {
    const _ = "Phunkie\\Lang\\PatternMatching::_";

    function match(...$values): Match
    {
        return new Match(...$values);
    }

    function conditionIsValid($condition, $value): bool
    {
        switch(true):
            case $condition === _:
            case sameTypeSameValue($condition, $value):
            case matchByReference($condition, $value):
                return true;
        endswitch;
        return false;
    }

    function sameTypeSameValue($condition, $value): bool
    {
        return gettype($condition) == gettype($value) && $value == $condition;
    }

    function matchByReference($condition, $value): bool
    {
        if ($condition instanceof Deconstructor) {
            return matchDeconstructor($condition, $value, $condition->class);
        }
        return false;
    }

    function matchDeconstructor($condition, $object, $class): bool
    {
        if ($condition instanceof Deconstructor && is_object($object) && get_class($object) === $class) {
            $reflected = new \ReflectionClass($object);
            $parameters = $reflected->getConstructor()->getParameters();
            for ($i = 1; $i <= count($parameters); $i++) {
                if (!$reflected->hasProperty($parameters[$i - 1]->getName())) {
                    throw new \Error("To use generic pattern matching you have to name the constructor argument as you ".
                        "have named the class property");
                }
                if (isset(((array) $object)["\0$class\0{$parameters[$i - 1]->getName()}"])) {
                    $condition->{"_$i"} = ((array)$object)["\0$class\0{$parameters[$i - 1]->getName()}"];
                } elseif (isset(((array)$object)["{$parameters[$i - 1]->getName()}"])) {
                    $condition->{"_$i"} = ((array)$object)["{$parameters[$i - 1]->getName()}"];
                }
            }
            return true;
        }
        return false;
    }
}