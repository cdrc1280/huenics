---
description: Enforce Filament v3/v4 unified action namespace imports (Filament\Actions) and strictly ban deprecated Filament\Tables\Actions namespace.
globs: app/Filament/**/*.php, resources/views/filament/**/*.blade.php
---

# Filament v3/v4 Action Namespace Invariants

## Strict Import Standard (Unified Actions)
In Filament v3 and v4, all actions are unified under `Filament\Actions\*`. **NEVER** import or reference `Filament\Tables\Actions\Action` or `Tables\Actions\Action`. Doing so causes a fatal `Class not found` error.

### Correct Filament v3/v4 Imports:
```php
// Unified Actions (Forms, Tables, Infolists, Pages)
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
```

### Strictly Banned Legacy Imports:
```php
// STRICTLY BANNED (Will cause fatal runtime error)
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
Tables\Actions\Action::make(...);
```

### Table Component Imports:
Only actual table structures (Columns, Filters, Summaries) use `Filament\Tables\*`:
```php
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Enums\RecordActionsPosition;
```
