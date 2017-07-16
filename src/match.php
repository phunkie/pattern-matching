<?php

namespace Phunkie\Lang\PatternMatching {

    use Phunkie\Lang\PatternMatching\Match;

    function match(...$values)
    {
        return new Match(...$values);
    }

    function conditionIsValid($condition, $value): bool
    {
        return sameTypeSameValue($condition, $value);
    }

    function sameTypeSameValue($condition, $value)
    {
        return gettype($condition) == gettype($value) && $value == $condition;
    }
}