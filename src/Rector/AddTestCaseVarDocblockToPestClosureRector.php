<?php

declare(strict_types=1);

namespace Madbox99\RectorFilament\Rector;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Adds a `/** @var TestCase $this *\/` docblock as the first line inside Pest
 * closures (it / test / beforeEach / afterEach) so IDEs and static analysers
 * resolve `$this` to the test case instead of reporting false positives.
 */
final class AddTestCaseVarDocblockToPestClosureRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const TEST_CASE_CLASS = 'test_case_class';

    /**
     * @var array<int, string>
     */
    private const PEST_FUNCTIONS = [
        'it',
        'test',
        'beforeEach',
        'afterEach',
    ];

    private string $testCaseClass = 'Tests\\TestCase';

    public function __construct(
        private readonly BetterNodeFinder $betterNodeFinder,
    ) {}

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Add a `@var TestCase $this` docblock to the top of Pest closures that use `$this`.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE'
beforeEach(function (): void {
    $this->user = User::factory()->create();
});
CODE,
                    <<<'CODE'
beforeEach(function (): void {
    /** @var \Tests\TestCase $this */
    $this->user = User::factory()->create();
});
CODE,
                    [self::TEST_CASE_CLASS => 'Tests\\TestCase']
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param  FuncCall  $node
     */
    public function refactor(Node $node): ?Node
    {
        $functionName = $this->getName($node->name);

        if ($functionName === null || ! in_array($functionName, self::PEST_FUNCTIONS, true)) {
            return null;
        }

        $closure = $this->findClosureArgument($node);

        if (! $closure instanceof Closure || $closure->stmts === []) {
            return null;
        }

        if (! $this->closureUsesThis($closure)) {
            return null;
        }

        $firstStmt = $closure->stmts[0];

        if ($this->hasThisVarTag($firstStmt)) {
            return null;
        }

        $firstStmt->setDocComment(new Doc(sprintf('/** @var \%s $this */', $this->testCaseClass)));

        return $node;
    }

    /**
     * @param  array<string, mixed>  $configuration
     */
    public function configure(array $configuration): void
    {
        $testCaseClass = $configuration[self::TEST_CASE_CLASS] ?? $this->testCaseClass;

        $this->testCaseClass = ltrim((string) $testCaseClass, '\\');
    }

    private function findClosureArgument(FuncCall $funcCall): ?Closure
    {
        foreach ($funcCall->getArgs() as $arg) {
            if ($arg->value instanceof Closure) {
                return $arg->value;
            }
        }

        return null;
    }

    private function closureUsesThis(Closure $closure): bool
    {
        return $this->betterNodeFinder->findFirst(
            $closure->stmts,
            fn (Node $subNode): bool => $subNode instanceof Variable && $this->getName($subNode) === 'this'
        ) instanceof Node;
    }

    private function hasThisVarTag(Node $node): bool
    {
        $docComment = $node->getDocComment();

        if (! $docComment instanceof Doc) {
            return false;
        }

        return (bool) preg_match('/@var\s+\S+\s+\$this\b/', $docComment->getText());
    }
}
