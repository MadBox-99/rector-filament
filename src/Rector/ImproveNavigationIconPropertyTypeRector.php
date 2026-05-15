<?php

declare(strict_types=1);

namespace Madbox99\RectorFilament\Rector;

use BackedEnum;
use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class ImproveNavigationIconPropertyTypeRector extends AbstractRector
{
    /**
     * @var array<int, string>
     */
    private const TARGET_PROPERTIES = [
        'navigationIcon',
        'activeNavigationIcon',
        'subNavigationIcon',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Widen `$navigationIcon` (and similar) property types to `string | BackedEnum | null`, the official Filament v5 type.',
            [
                new CodeSample(
                    <<<'CODE'
protected static ?string $navigationIcon = 'heroicon-o-user';
CODE,
                    <<<'CODE'
protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user';
CODE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param  Property  $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->isStatic()) {
            return null;
        }

        $hasTargetName = false;

        foreach ($node->props as $prop) {
            if (in_array($prop->name->toString(), self::TARGET_PROPERTIES, true)) {
                $hasTargetName = true;

                break;
            }
        }

        if (! $hasTargetName) {
            return null;
        }

        if ($this->isAlreadyDesiredType($node->type)) {
            return null;
        }

        if (! $this->isNarrowStringNullableType($node->type)) {
            return null;
        }

        $node->type = new UnionType([
            new Identifier('string'),
            new FullyQualified(BackedEnum::class),
            new Identifier('null'),
        ]);

        return $node;
    }

    private function isAlreadyDesiredType(null|Identifier|Node\Name|ComplexType $type): bool
    {
        if (! $type instanceof UnionType) {
            return false;
        }

        $names = [];

        foreach ($type->types as $part) {
            if ($part instanceof Identifier) {
                $names[] = $part->toLowerString();
            } elseif ($part instanceof Node\Name) {
                $names[] = ltrim($this->getName($part) ?? '', '\\');
            }
        }

        sort($names);

        return $names === ['BackedEnum', 'null', 'string'];
    }

    private function isNarrowStringNullableType(null|Identifier|Node\Name|ComplexType $type): bool
    {
        if ($type === null) {
            return true;
        }

        if ($type instanceof IntersectionType) {
            return false;
        }

        if ($type instanceof NullableType) {
            $inner = $type->type;

            return $inner instanceof Identifier && $inner->toLowerString() === 'string';
        }

        if ($type instanceof Identifier) {
            return $type->toLowerString() === 'string';
        }

        return false;
    }
}
