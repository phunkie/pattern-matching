<?php declare(strict_types=1);

namespace Phunkie\Lang\PatternMatching {

    use Phunkie\ADT\SumTypeTag;
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
            case matchAndDeconstruct($condition, $value):
            case matchWithWildcard($condition, $value):
            case matchConstantToObject($condition, $value):
                return true;
        endswitch;
        return false;
    }

    function sameTypeSameValue($condition, $value): bool
    {
        return gettype($condition) == gettype($value) && $value == $condition;
    }

    function matchWithWildcard($condition, $object)
    {
        if ($condition instanceof Wildcard) {
            $class = $condition->class;
            if (is_object($object) && get_class($object) === $class) {
                $reflected = new \ReflectionClass($object);
                $parameters = $reflected->getConstructor()->getParameters();
                for ($i = 1; $i <= count($parameters); $i++) {
                    if (!$reflected->hasProperty($parameters[$i - 1]->getName())) {
                        throw new \Error("To use generic pattern matching you have to name the constructor argument as you " .
                            "have named the class property");
                    }
                    if ($condition->{"_$i"} == _) {
                        continue;
                    }
                    if (isset(((array)$object)["\0$class\0{$parameters[$i - 1]->getName()}"])) {
                        if (!sameTypeSameValue($condition->{"_$i"}, ((array)$object)["\0$class\0{$parameters[$i - 1]->getName()}"])) {
                            return false;
                        }
                    } elseif (isset(((array)$object)["{$parameters[$i - 1]->getName()}"])) {
                        if (!sameTypeSameValue($condition->{"_$i"}, ((array)$object)["{$parameters[$i - 1]->getName()}"])) {
                            return false;
                        }
                    }
                }
                return true;
            }
            return false;
        }
        return false;
    }

    function matchAndDeconstruct($condition, $object): bool
    {
        if ($condition instanceof Deconstructor) {
            $class = $condition->class;
            if (is_object($object) && get_class($object) === $class) {
                $reflected = new \ReflectionClass($object);
                $parameters = $reflected->getConstructor()->getParameters();
                for ($i = 1; $i <= count($parameters); $i++) {
                    if (!$reflected->hasProperty($parameters[$i - 1]->getName())) {
                        throw new \Error("To use generic pattern matching you have to name the constructor argument as you " .
                            "have named the class property");
                    }
                    if (isset(((array)$object)["\0$class\0{$parameters[$i - 1]->getName()}"])) {
                        $condition->{"_$i"} = ((array)$object)["\0$class\0{$parameters[$i - 1]->getName()}"];
                    } elseif (isset(((array)$object)["{$parameters[$i - 1]->getName()}"])) {
                        $condition->{"_$i"} = ((array)$object)["{$parameters[$i - 1]->getName()}"];
                    }
                }
                return true;
            }
            return false;
        }
        return false;
    }

    function matchConstantToObject($condition, $object)
    {
        if (is_string($condition) && is_object($object)) {
            if (strpos($condition, 'Phunkie@Reserved@Constant@') === 0) {
                return substr($condition, strlen('Phunkie@Reserved@Constant@')) === get_class($object);
            }
        }
        return false;
    }

    function assertExhausted($case, $hash)
    {
        if ($case instanceof SumTypeTag) {
            $notCheckedYet = $case::notCheckedYet($hash);
            if (count($notCheckedYet) > 1) {
                trigger_error("warning: match may not be exhaustive.\n" .
                    "Patterns not matched: [" . implode(", ", $notCheckedYet) . "]", E_USER_WARNING);
            }
        }
    }
}