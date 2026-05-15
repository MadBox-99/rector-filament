<?php

declare(strict_types=1);

namespace Cegem360\RectorFilament\Rector;

use Override;
use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class AddOverrideAttributeToFilamentMethodsRector extends AbstractRector
{
    /**
     * @var array<int, string>
     */
    private const TARGET_METHODS = [
        'form',
        'infolist',
        'table',
        'getNavigationLabel',
        'getModelLabel',
        'getPluralModelLabel',
        'getRelations',
        'getPages',
        'getHeaderActions',
        'getFooterActions',
        'getRecordRouteBindingEloquentQuery',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Add #[Override] attribute to common Filament Resource / Page / RelationManager methods that override their parent.',
            [
                new CodeSample(
                    <<<'CODE'
final class UserResource extends Resource
{
    public static function getNavigationLabel(): string
    {
        return __('Users');
    }
}
CODE,
                    <<<'CODE'
final class UserResource extends Resource
{
    #[\Override]
    public static function getNavigationLabel(): string
    {
        return __('Users');
    }
}
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
        return [ClassMethod::class];
    }

    /**
     * @param  ClassMethod  $node
     */
    public function refactor(Node $node): ?Node
    {
        $methodName = $node->name->toString();

        if (! in_array($methodName, self::TARGET_METHODS, true)) {
            return null;
        }

        if ($this->hasOverrideAttribute($node)) {
            return null;
        }

        $node->attrGroups[] = new AttributeGroup([
            new Attribute(new FullyQualified(Override::class)),
        ]);

        return $node;
    }

    private function hasOverrideAttribute(ClassMethod $node): bool
    {
        foreach ($node->attrGroups as $group) {
            foreach ($group->attrs as $attribute) {
                if ($this->getName($attribute->name) === Override::class) {
                    return true;
                }
            }
        }

        return false;
    }
}
