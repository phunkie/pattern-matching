<?php

namespace Referenced {

    use DeconstructionExample;
    use Phunkie\Lang\PatternMatching\Deconstructor;

    function DeconstructionExample(&$x, &$y)
    {
        return new Deconstructor(DeconstructionExample::class, $x, $y);
    }
}

namespace {

    use function Phunkie\Lang\PatternMatching\match;
    use const Phunkie\Lang\PatternMatching\_;
    use function Referenced\DeconstructionExample as DeconstructionExampleCase;

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

    describe("Pattern matching", function () {
        context("Switch behaviour", function () {
            it("matches a simple value with a condition", function () {
                $x = 1;
                $y = null;

                $on = match($x);
                switch (true) {
                    case $on(1):
                        $y = "one";
                        break;
                }

                expect($y)->toBe("one");
            });


            it("matches one in multiple cases", function () {
                $x = 1;
                $y = null;

                $on = match($x);
                switch (true) {
                    case $on(1):
                        $y = "one";
                        break;
                    case $on(2):
                        $y = "two";
                        break;
                    case $on(3):
                        $y = "three";
                        break;
                }

                expect($y)->toBe("one");
            });


            it("matches one in multiple cases with default clause", function () {
                $x = 4;
                $y = null;

                $on = match($x);
                switch (true) {
                    case $on(1):
                        $y = "one";
                        break;
                    case $on(2):
                        $y = "two";
                        break;
                    case $on(_):
                        $y = "many";
                        break;
                }

                expect($y)->toBe("many");
            });
        });
        context("Deconstruction", function () {

            it("deconstructs classes", function () {
                $example = new DeconstructionExample(42, "Chuck");
                $on = match($example);
                switch (true) {
                    case $on(DeconstructionExampleCase($a, $b)):
                        expect($a)->toBe(42);
                        expect($b)->toBe("Chuck");
                        return;
                }
                throw new \Exception("Not deconstructed correctly");
            });
        });
    });
}