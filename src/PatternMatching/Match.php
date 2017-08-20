<?php

namespace Phunkie\Lang\PatternMatching;

class Match
{
    private $values;

    public function __construct(...$values)
    {
        $this->values = $values;
    }

    public function __invoke(...$conditions): bool
    {
        for ($position = 0; $position < count($conditions); $position++) {
            if (!conditionIsValid($conditions[$position], $this->values[$position])) {
                return false;
            }
        }
        return true;
    }
}