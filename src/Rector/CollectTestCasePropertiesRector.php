<?php

declare(strict_types=1);

namespace Madbox99\RectorFilament\Rector;

use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\PropertyItem;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\PhpParser\Parser\RectorParser;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Declares typed public properties on the base test case for every `$this->x = ...`
 * assignment found in the surrounding test suite, so IDEs and static analysers know
 * the shape of the shared test state. Types are inferred from model factories,
 * `new` expressions and scalar literals; the referenced classes are emitted as
 * fully-qualified names so name-importing turns them into `use` statements.
 */
final class CollectTestCasePropertiesRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const TEST_CASE_CLASS = 'test_case_class';

    /**
     * @var string
     */
    public const SCAN_PATHS = 'scan_paths';

    private string $testCaseClass = 'Tests\\TestCase';

    /**
     * @var array<int, string>
     */
    private array $scanPaths = [];

    public function __construct(
        private readonly RectorParser $rectorParser,
    ) {}

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Collect `$this->` assignments from the test suite and declare them as typed public properties on the base test case.',
            [
                new ConfiguredCodeSample(
                    <<<'CODE'
abstract class TestCase extends BaseTestCase
{
}
CODE,
                    <<<'CODE'
abstract class TestCase extends BaseTestCase
{
    public \App\Models\User $user;
}
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
        return [Class_::class];
    }

    /**
     * @param  Class_  $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->getName($node) !== $this->testCaseClass) {
            return null;
        }

        $existingPropertyNames = $this->resolveExistingPropertyNames($node);
        $inferredTypes = $this->collectInferredPropertyTypes();

        $newProperties = [];

        foreach ($inferredTypes as $propertyName => $typeNode) {
            if ($typeNode === null || in_array($propertyName, $existingPropertyNames, true)) {
                continue;
            }

            $newProperties[] = $this->createPublicProperty($propertyName, $typeNode);
        }

        if ($newProperties === []) {
            return null;
        }

        $node->stmts = $this->insertProperties($node->stmts, $newProperties);

        return $node;
    }

    /**
     * @param  array<string, mixed>  $configuration
     */
    public function configure(array $configuration): void
    {
        $testCaseClass = $configuration[self::TEST_CASE_CLASS] ?? $this->testCaseClass;
        $this->testCaseClass = ltrim((string) $testCaseClass, '\\');

        /** @var array<int, string> $scanPaths */
        $scanPaths = $configuration[self::SCAN_PATHS] ?? [];
        $this->scanPaths = $scanPaths;
    }

    /**
     * @return array<int, string>
     */
    private function resolveExistingPropertyNames(Class_ $class): array
    {
        $names = [];

        foreach ($class->getProperties() as $property) {
            foreach ($property->props as $propertyItem) {
                $names[] = $propertyItem->name->toString();
            }
        }

        return $names;
    }

    /**
     * Walk every test file in scope and collect the inferred type per `$this->` property.
     * Conflicting inferences for the same name collapse to `null` (skipped).
     *
     * @return array<string, Identifier|Name|null>
     */
    private function collectInferredPropertyTypes(): array
    {
        /** @var array<string, Identifier|Name|null> $types */
        $types = [];
        /** @var array<string, string|null> $signatures */
        $signatures = [];

        foreach ($this->collectThisAssignments() as $propertyName => $expressions) {
            foreach ($expressions as $expression) {
                $typeNode = $this->inferType($expression);
                $signature = $typeNode instanceof Node ? $this->typeSignature($typeNode) : null;

                if (! array_key_exists($propertyName, $signatures)) {
                    $signatures[$propertyName] = $signature;
                    $types[$propertyName] = $typeNode;

                    continue;
                }

                if ($signatures[$propertyName] !== $signature) {
                    $types[$propertyName] = null;
                }
            }
        }

        return $types;
    }

    /**
     * Properties that a class-based test declares itself are left out: lifting them
     * onto the base test case would clash with the subclass's own declaration.
     *
     * @return array<string, array<int, Node>>
     */
    private function collectThisAssignments(): array
    {
        $nodeFinder = new NodeFinder();
        $assignmentsByProperty = [];
        $declaredPropertyNames = [];

        foreach ($this->resolveScanFiles() as $filePath) {
            $stmts = $this->parseFileWithResolvedNames($filePath);

            /** @var Assign[] $assigns */
            $assigns = $nodeFinder->findInstanceOf($stmts, Assign::class);

            foreach ($assigns as $assign) {
                $propertyName = $this->matchThisPropertyName($assign);

                if ($propertyName === null) {
                    continue;
                }

                $assignmentsByProperty[$propertyName][] = $assign->expr;
            }

            /** @var Class_[] $classes */
            $classes = $nodeFinder->findInstanceOf($stmts, Class_::class);

            foreach ($classes as $class) {
                if ($class->namespacedName?->toString() === $this->testCaseClass) {
                    continue;
                }

                array_push($declaredPropertyNames, ...$this->resolveExistingPropertyNames($class));
            }
        }

        return array_diff_key($assignmentsByProperty, array_flip($declaredPropertyNames));
    }

    private function matchThisPropertyName(Assign $assign): ?string
    {
        $propertyFetch = $assign->var;

        if (! $propertyFetch instanceof PropertyFetch) {
            return null;
        }

        if (! $propertyFetch->var instanceof Variable || $propertyFetch->var->name !== 'this') {
            return null;
        }

        if (! $propertyFetch->name instanceof Identifier) {
            return null;
        }

        return $propertyFetch->name->toString();
    }

    private function inferType(Node $expression): Identifier|Name|null
    {
        if ($expression instanceof New_ && $expression->class instanceof Name) {
            return new FullyQualified($expression->class->toString());
        }

        if ($expression instanceof String_) {
            return new Identifier('string');
        }

        if ($expression instanceof Int_) {
            return new Identifier('int');
        }

        if ($expression instanceof Float_) {
            return new Identifier('float');
        }

        if ($expression instanceof ConstFetch && in_array($expression->name->toLowerString(), ['true', 'false'], true)) {
            return new Identifier('bool');
        }

        return $this->inferFactoryType($expression);
    }

    /**
     * Resolve `Model::factory()->...->create()` chains to the model type,
     * or to an Eloquent collection when `->count()` widens the result.
     */
    private function inferFactoryType(Node $expression): ?Name
    {
        $isCollection = false;
        $current = $expression;

        while ($current instanceof MethodCall) {
            if ($current->name instanceof Identifier && $current->name->toString() === 'count') {
                $isCollection = true;
            }

            $current = $current->var;
        }

        if (! $current instanceof StaticCall) {
            return null;
        }

        if (! $current->name instanceof Identifier || $current->name->toString() !== 'factory') {
            return null;
        }

        if (! $current->class instanceof Name) {
            return null;
        }

        if ($isCollection) {
            return new FullyQualified('Illuminate\\Database\\Eloquent\\Collection');
        }

        return new FullyQualified($current->class->toString());
    }

    private function typeSignature(Identifier|Name $typeNode): string
    {
        return $typeNode->toString();
    }

    private function createPublicProperty(string $propertyName, Identifier|Name $typeNode): Property
    {
        return new Property(
            Modifiers::PUBLIC,
            [new PropertyItem($propertyName)],
            [],
            $typeNode
        );
    }

    /**
     * Insert the new properties after the last existing property, or at the top of the class.
     *
     * @param  array<int, Node\Stmt>  $stmts
     * @param  array<int, Property>  $newProperties
     * @return array<int, Node\Stmt>
     */
    private function insertProperties(array $stmts, array $newProperties): array
    {
        $lastPropertyIndex = -1;

        foreach ($stmts as $index => $stmt) {
            if ($stmt instanceof Property) {
                $lastPropertyIndex = $index;
            }
        }

        $insertAt = $lastPropertyIndex + 1;

        array_splice($stmts, $insertAt, 0, $newProperties);

        return $stmts;
    }

    /**
     * @return array<int, string>
     */
    private function resolveScanFiles(): array
    {
        $paths = $this->scanPaths;

        if ($paths === []) {
            $paths = [dirname($this->getFile()->getFilePath())];
        }

        $files = [];

        foreach ($paths as $path) {
            if (is_file($path)) {
                $files[] = $path;

                continue;
            }

            if (! is_dir($path)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $fileInfo) {
                if ($fileInfo instanceof \SplFileInfo && $fileInfo->getExtension() === 'php') {
                    $files[] = $fileInfo->getPathname();
                }
            }
        }

        sort($files);

        return $files;
    }

    /**
     * @return array<int, Node\Stmt>
     */
    private function parseFileWithResolvedNames(string $filePath): array
    {
        $stmts = $this->rectorParser->parseFile($filePath);

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NameResolver());

        return $nodeTraverser->traverse($stmts);
    }
}
