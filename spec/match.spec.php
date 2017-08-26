<?php

namespace {

    use function Fixture\ExhaustiveAnalysis\Friday;
    use function Fixture\ExhaustiveAnalysis\Monday;
    use function Fixture\ExhaustiveAnalysis\Saturday;
    use function Fixture\ExhaustiveAnalysis\Sunday;
    use function Fixture\ExhaustiveAnalysis\Thursday;
    use function Fixture\ExhaustiveAnalysis\Tuesday;
    use function Fixture\ExhaustiveAnalysis\Wednesday;
    use Fixture\WildcardExample;
    use function Phunkie\Lang\PatternMatching\assertExhausted;
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

        context("Exhaustive analysis", function() {

            it ("produces a warning if exhaustive analysis does not cover all cases", function() {
                expect(function() {
                    $weekday = new \Fixture\Wednesday();
                    $on = match($weekday); $hash = md5(serialize($on)); switch(true) {
                        case $on(Sunday($hash)): break;
                        case $on(Monday($hash)): break;
                        default: assertExhausted($weekday, $hash);
                    }
                })->toThrow(new Kahlan\PhpErrorException("`E_USER_WARNING` warning: match may not be exhaustive.\n" .
                    "Patterns not matched: [Fixture\\Tuesday, Fixture\\Wednesday, Fixture\\Thursday, Fixture\\Friday, " .
                    "Fixture\\Saturday]"));
            });

            it ("does not produce the warning when all cases have been covered", function() {
                expect(function() {
                    $weekday = new \Fixture\Wednesday();
                    $on = match($weekday); $hash = md5(serialize($on)); switch(true) {
                        case $on(Sunday($hash)): break;
                        case $on(Monday($hash)): break;
                        case $on(Tuesday($hash)): break;
                        case $on(Wednesday($hash)): break;
                        case $on(Thursday($hash)): break;
                        case $on(Friday($hash)): break;
                        case $on(Saturday($hash)): break;
                        default: assertExhausted($weekday, $hash);
                    }
                })->not->toThrow();
            });
        });
    });
}

namespace Fixture {

    use Phunkie\ADT\ImmutableSealed;
    use Phunkie\ADT\SumType;
    use Phunkie\ADT\SumTypeTag;
    use Phunkie\ADT\TypeConstructor;

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

    abstract class Weekday extends ImmutableSealed implements TypeConstructor, SumTypeTag {
        use \Phunkie\ADT\ExhaustiveAnalysis;
        const sealedTo = [ Sunday::class, Monday::class, Tuesday::class,
            Wednesday::class, Thursday::class, Friday::class, Saturday::class];
        public function __construct() { $this->applySeal(); }
    }
    final class Sunday extends Weekday { use SumType; const typeConstructor = Weekday::class; }
    final class Monday extends Weekday { use SumType; const typeConstructor = Weekday::class; }
    final class Tuesday extends Weekday { use SumType; const typeConstructor = Weekday::class; }
    final class Wednesday extends Weekday { use SumType; const typeConstructor = Weekday::class; }
    final class Thursday extends Weekday { use SumType; const typeConstructor = Weekday::class; }
    final class Friday extends Weekday { use SumType; const typeConstructor = Weekday::class; }
    final class Saturday extends Weekday { use SumType; const typeConstructor = Weekday::class; }
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

namespace Fixture\ExhaustiveAnalysis {

    use Fixture\Friday;
    use Fixture\Monday;
    use Fixture\Saturday;
    use Fixture\Sunday;
    use Fixture\Thursday;
    use Fixture\Tuesday;
    use Fixture\Wednesday;
    use Phunkie\Lang\PatternMatching\Wildcard;

    function Sunday($hash) {
        Sunday::markChecked($hash);
        return new Wildcard(Sunday::class, ...func_get_args());
    }

    function Monday($hash) {
        Monday::markChecked($hash);
        return new Wildcard(Monday::class, ...func_get_args());
    }

    function Tuesday($hash) {
        Monday::markChecked($hash);
        return new Wildcard(Tuesday::class, ...func_get_args());
    }

    function Wednesday($hash) {
        Monday::markChecked($hash);
        return new Wildcard(Wednesday::class, ...func_get_args());
    }

    function Thursday($hash) {
        Monday::markChecked($hash);
        return new Wildcard(Thursday::class, ...func_get_args());
    }

    function Friday($hash) {
        Monday::markChecked($hash);
        return new Wildcard(Friday::class, ...func_get_args());
    }

    function Saturday($hash) {
        Monday::markChecked($hash);
        return new Wildcard(Saturday::class, ...func_get_args());
    }
}