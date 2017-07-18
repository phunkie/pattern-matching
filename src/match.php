<?php

namespace Phunkie\Lang\PatternMatching {

    use Phunkie\Lang\PatternMatching\Match;

    const _ = "Phunkie\\Lang\\PatternMatching::_";

    function match(...$values)
    {
        return new Match(...$values);
    }

    function conditionIsValid($condition, $value): bool
    {
        switch(true):
            case $condition === _:
            case sameTypeSameValue($condition, $value):
                return true;
        endswitch;
        return false;
    }

    function sameTypeSameValue($condition, $value)
    {
        return gettype($condition) == gettype($value) && $value == $condition;
    }
}