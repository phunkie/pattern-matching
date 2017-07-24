<?php

namespace {

    use Fixture\WildcardExample;
    use function Phunkie\Lang\PatternMatching\match;
    use const Phunkie\Lang\PatternMatching\_;
    use Fixture\DeconstructionExample;
    use function Fixture\Referenced\DeconstructionExample as DeconstructionExampleCase;
    use function Fixture\Wildcardeded\WildcardExample as WildcardExampleCase;

    describe("Pattern matching", function () {

        context("Switch behaviour", function () {

            it("matches a simple value with a condition", function () {
                $x = 1;
                $y = null;

                $on = match($x); switch (true) {
                    case $on(1): $y = "one"; break;
                }

                expect($y)->toBe("one");
            });

            it("matches one in multiple cases", function () {
                $x = 1;
                $y = null;

                $on = match($x); switch (true) {
                    case $on(1): $y = "one"; break;
                    case $on(2): $y = "two"; break;
                    case $on(3): $y = "three"; break;
                }

                expect($y)->toBe("one");
            });

            it("matches one in multiple cases with default clause", function () {
                $x = 4;
                $y = null;

                $on = match($x); switch (true) {
                    case $on(1): $y = "one"; break;
                    case $on(2): $y = "two"; break;
                    case $on(_): $y = "many"; break;
                }

                expect($y)->toBe("many");
            });
        });

        context("Deconstruction", function () {

            it("deconstructs classes", function () {
                $example = new DeconstructionExample(42, "Chuck");
                $on = match($example); switch (true) {
                    case $on(DeconstructionExampleCase($a, $b)):
                        expect($a)->toBe(42);
                        expect($b)->toBe("Chuck");
                        return;
                }
                throw new \Exception("Not deconstructed correctly");
            });
        });

        context("Wildcard", function() {
            $success = true;
            it("matches any property with wildcards", function() use ($success) {
                $example = new WildcardExample(42, "Chuck");
                $on = match($example); switch (true) {
                    case $on(WildcardExampleCase(_, "Chuck")):
                        $success = $success && true; break;
                }

                $on = match($example); switch (true) {
                    case $on(WildcardExampleCase(42, _)):
                        $success = $success && true; break;
                }

                $on = match($example); switch (true) {
                    case $on(WildcardExampleCase(_, _)):
                        $success = $success && true; break;
                }

                $on = match($example); switch (true) {
                    case $on(WildcardExampleCase(_, "Jack")):
                        $success = $success && false; break;
                }

                $on = match($example); switch (true) {
                    case $on(WildcardExampleCase(43, _)):
                        $success = $success && false; break;
                }

                $on = match($example); switch (true) {
                    case $on(WildcardExampleCase(43, "Jack")):
                        $success = $success && false; break;
                }

                $on = match($example); switch (true) {
                    case $on(WildcardExampleCase(43)):
                        $success = $success && false; break;
                }
                
                expect($success)->toBe(true);
            });
        });

        context("Matching constants representing types", function() {

            it("matches some magic constants to object", function() {

                $success = true;

                define("SomeType", "Phunkie@Reserved@Constant@SomeType");
                class SomeType {}
                $example1 = new SomeType;

                $on = match($example1); switch(true) {
                    case $on(SomeType): break;
                    case $on(_): $success = false; break;
                }

                expect($success)->toBe(true);
            });

            it("does not match constants missing magic prefix", function() {

                $success = true;

                define("SomeOtherType", "SomeOtherType");
                class SomeOtherType {}
                $example2 = new SomeOtherType;

                $on = match($example2); switch(true) {
                    case $on(SomeOtherType): $success = false; break;
                    case $on(_): break;
                }

                expect($success)->toBe(true);
            });
        });
    });
}

namespace Fixture {
    class DeconstructionExample
    {
        private $x;
        private $y;

        public function __construct($x, $y)
        {
            $this->x = $x;
            $this->y = $y;
        }
    }

    class WildcardExample
    {
        private $x;
        private $y;

        public function __construct($x, $y)
        {
            $this->x = $x;
            $this->y = $y;
        }
    }
}

namespace Fixture\Referenced {

    use Fixture\DeconstructionExample;
    use Phunkie\Lang\PatternMatching\Deconstructor;

    function DeconstructionExample(&$x, &$y)
    {
        return new Deconstructor(DeconstructionExample::class, $x, $y);
    }
}

namespace Fixture\Wildcardeded {

    use Fixture\WildcardExample;
    use Phunkie\Lang\PatternMatching\Wildcard;

    function WildcardExample()
    {
        return new Wildcard(WildcardExample::class, ...func_get_args());
    }
}