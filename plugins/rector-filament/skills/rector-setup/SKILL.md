---
name: rector-setup
description: Use when creating or cleaning up rector.php in a Laravel/Filament project (new site/app, new project from a template, or "rendbe tenni a rectort"), when adding madbox-99/rector-filament, or when a rector.php lists LaravelSetList/SetList entries that duplicate FilamentSetList::LARAVEL_APP or withPreparedSets.
---

# Rector beállítás Laravel/Filament projektben

## Alapszabály

A cégprojektek a `madbox-99/rector-filament` csomag `FilamentSetList::LARAVEL_APP` setjét használják Laravel-setként. **Semmi mást ne sorolj fel mellette, amit már tartalmaz**, és ne vegyél fel olyan `SetList::` sort, amit a `withPreparedSets` is betölt.

A `LARAVEL_APP` (v1.4.1) tartalma — ezek közül egyik se kerüljön külön a `rector.php`-ba:
- `LaravelSetList::LARAVEL_CODE_QUALITY`, `LARAVEL_COLLECTION`, `LARAVEL_TYPE_DECLARATIONS`, `LARAVEL_IF_HELPERS`
- `LARAVEL_ELOQUENT_MAGIC_METHOD_TO_QUERY_BUILDER`
- `LARAVEL_ARRAYACCESS_TO_METHOD_CALL`, `LARAVEL_CONTAINER_STRING_TO_FULLY_QUALIFIED_NAME`, `LARAVEL_FACADE_ALIASES_TO_FULL_NAMES`
- `AddExtendsAnnotationToModelFactoriesRector`
- `FilamentSetList::FILAMENT_CODE_QUALITY`, `FilamentSetList::FILAMENT_TESTS`

Elavult, ne vedd fel: `LARAVEL_ARRAY_STR_FUNCTION_TO_STATIC_CALL` (Laravel 6 előtti helperek), `LARAVEL_LEGACY_FACTORIES_TO_CLASSES` (Laravel 8 előtti factoryk), `LARAVEL_FACTORIES` (az `AddHasFactoryToModelsRector` factory nélküli modellekre is `HasFactory`-t tesz).

`withPreparedSets` ↔ `SetList` megfeleltetés (ha a kapcsoló `true`, a `SetList::` sor duplikátum, töröld):
`deadCode`→`DEAD_CODE`, `codeQuality`→`CODE_QUALITY`, `codingStyle`→`CODING_STYLE`, `typeDeclarations`→`TYPE_DECLARATION`, `privatization`→`PRIVATIZATION`, `earlyReturn`→`EARLY_RETURN`, `instanceOf`→`INSTANCEOF`, `naming`→`NAMING`, `carbon`→`CARBON`, `rectorPreset`→`RECTOR_PRESET`.

## Mintakonfiguráció (új projekt)

```php
<?php

declare(strict_types=1);

use Madbox99\RectorFilament\Set\FilamentSetList;
use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withComposerBased(laravel: true)
    ->withImportNames()
    ->withPhpSets(php84: true)
    ->withParallel()
    ->withSets([
        FilamentSetList::LARAVEL_APP,
    ])
    ->withPaths([
        __DIR__.'/app',
        __DIR__.'/tests',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/routes',
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        codingStyle: true,
        typeDeclarations: true,
        rectorPreset: true,
        phpunitCodeQuality: true
    );
```

- `withComposerBased(laravel: true)` hozza a telepített Laravel-verzió upgrade-szabályait; a `withSetProviders(LaravelSetProvider::class)` elavult, ne használd.
- A PHP-szint a `composer.json` `php` megkötéséhez igazodjon (`php84`, `php85`, …).
- Ha `privatization: true` is kell: tedd mellé a skipet, mert a Filament/Livewire a hookokat (`afterCreate`, `afterSave`, `beforeValidate`, `rules()`) a szülőosztályból hívja:
  ```php
  ->withSkip([
      PrivatizeFinalClassMethodRector::class => [__DIR__.'/app/Filament', __DIR__.'/app/Livewire'],
  ])
  ```

## composer.json

```json
"require-dev": { "madbox-99/rector-filament": "^1.4", "rector/rector": "^2.7", "driftingly/rector-laravel": "^2.6" },
"scripts": {
    "fix": ["./vendor/bin/rector process", "./vendor/bin/pint"],
    "test": ["@php artisan config:clear --ansi", "./vendor/bin/pint --test", "./vendor/bin/rector process --dry-run", "./vendor/bin/pest"]
}
```

A `fix` sorrendje: **előbb rector, utána pint** (fordítva a rector formázatlan kódot hagy, és a `pint --test` elbukik).

## Meglévő rector.php rendbetétele

1. Töröld a `LARAVEL_APP` által már tartalmazott és az elavult `LaravelSetList::` sorokat; ha a `LaravelSetList` már nincs használva, az importját is.
2. Töröld a `withPreparedSets`-szel duplikált `SetList::` sorokat; ha a `SetList` már nincs használva, az importját is.
3. `composer fix`, majd `composer test` (vagy a teljes tesztkészlet). Push előtt a globális pre-push hook (`~/.config/git/hooks/pre-push`) úgyis lefuttatja.

## Ismert Rector-buktatók (fix után ellenőrizd)

- `CarbonToDateFacadeRector`: `Date::parse` CarbonImmutable-t adhat, ami ütközik a `Carbon` visszatérési típussal (catch `Throwable` mögött csendes `null`) → `?CarbonInterface` típus vagy skip.
- `StringToClassConstantRector` (Laravel 5-ös szint-set): `view('auth.login')` → `view(Login::class)` → skip a FortifyServiceProvider-re.
- Típusdeklaráló szabály: `Http::assertSent(function ($r)` → `array $r`; a helyes típus `Illuminate\Http\Client\Request`.
- `TernaryToNullsafeCoalesceRector`: a PHPStan „nullsafe.neverNull” hibát adhat → skip az érintett fájlra.
- Több projekt párhuzamos fixelésénél munkánként külön `TMPDIR`, különben a Rector-cache ütközik.
