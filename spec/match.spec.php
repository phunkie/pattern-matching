<?php

use function \Phunkie\Lang\PatternMatching\match;

describe("match", function() {
    context("behaving like a switch", function() {
        it("matches a simple value with a condition", function () {
            $x = 1;
            $y = null;

            $on = match($x); switch(true) {
                case $on(1): $y = "one"; break;
            }

            expect($y)->toBe("one");
        });


        it("multiple cases", function () {
            $x = 1;
            $y = null;

            $on = match($x); switch(true) {
                case $on(1): $y = "one"; break;
                case $on(2): $y = "two"; break;
                case $on(3): $y = "two"; break;
            }

            expect($y)->toBe("one");
        });
    });
});