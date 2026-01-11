# BaseResource Filament

## Overview

`BaseResource` is an abstract base class that provides common functionality for all Filament resources in the NoFlaye MVP application. It standardizes table actions, bulk actions, pagination, and common behaviors across all resources, reducing code duplication and ensuring consistency.

## Features

- **Standard CRUD Actions**: View, Edit, Delete actions pre-configured
- **Soft Delete Support**: Optional Restore and Force Delete actions
- **Bulk Actions**: Delete, Restore, and Force Delete bulk operations
- **Configurable Pagination**: Customizable default page sizes and options
- **Session Persistence**: Filters, search, and sort state persist across requests
- **Global Search**: Built-in support with configurable result limits
- **Permission-Aware**: Actions automatically respect Filament policies

## Table of Contents

1. [Basic Usage](#basic-usage)
2. [Configuration Options](#configuration-options)
3. [Customizing Actions](#customizing-actions)
4. [Soft Deletes](#soft-deletes)
5. [Pagination](#pagination)
6. [Global Search](#global-search)
7. [Migration Guide](#migration-guide)
8. [Examples](#examples)

---

## Basic Usage

### Creating a New Resource

Extend `BaseResource` instead of the standard Filament `Resource`:

```php
<?php

namespace App\Filament\Resources;

use App\Filament\BaseResource;
use App\Models\Shop;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ShopResource extends BaseResource
{
    protected static ?string $model = Shop::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';

    protected static ?string $navigationGroup = 'Entities Management';

    public static function form(Schema $form): Schema
    {
        return $form->components([
            // Your form components
        ]);
    }

    public static function table(Table $table): Table
    {
        return static::configureTable($table)
            ->columns([
                // Your table columns
            ])
            ->filters([
                // Your filters
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListShops::route('/'),
            'create' => Pages\CreateShop::route('/create'),
            'view' => Pages\ViewShop::route('/{record}'),
            'edit' => Pages\EditShop::route('/{record}/edit'),
        ];
    }
}
```

**Key Points:**
- Call `static::configureTable($table)` in your `table()` method
- This applies all default actions, bulk actions, and pagination settings
- Chain additional configuration after `configureTable()`

---

## Configuration Options

### Protected Properties

BaseResource provides several protected properties you can override:

#### 1. **Soft Deletes Support**

```php
protected static bool $supportsSoftDeletes = false;
```

Enable to add Restore and Force Delete actions (default: `false`)

**Example:**
```php
class UserResource extends BaseResource
{
    protected static bool $supportsSoftDeletes = true; // Enable soft delete actions
}
```

#### 2. **Default Records Per Page**

```php
protected static int $defaultRecordsPerPage = 25;
```

Set the default number of records shown per page (default: `25`)

**Example:**
```php
class ProductResource extends BaseResource
{
    protected static int $defaultRecordsPerPage = 50; // Show 50 records by default
}
```

#### 3. **Pagination Options**

```php
protected static array $paginationOptions = [10, 25, 50, 100];
```

Available page size options in the table footer (default: `[10, 25, 50, 100]`)

**Example:**
```php
class OrderResource extends BaseResource
{
    protected static array $paginationOptions = [25, 50, 100, 250];
}
```

---

## Customizing Actions

### Default Record Actions

Override `getDefaultRecordActions()` to customize row-level actions:

```php
protected static function getDefaultRecordActions(): array
{
    return [
        ...parent::getDefaultRecordActions(),
        Action::make('archive')
            ->icon('heroicon-o-archive-box')
            ->action(fn (Model $record) => $record->archive()),
    ];
}
```

**Default actions provided:**
- `ViewAction::make()` - View record details
- `EditAction::make()` - Edit record
- `DeleteAction::make()` - Delete record
- `RestoreAction::make()` - Restore soft-deleted record (if soft deletes enabled)
- `ForceDeleteAction::make()` - Permanently delete record (if soft deletes enabled)

### Default Toolbar Actions (Bulk Actions)

Override `getDefaultToolbarActions()` to customize bulk actions:

```php
protected static function getDefaultToolbarActions(): array
{
    return [
        BulkActionGroup::make([
            ...parent::getDefaultToolbarActions()[0]->getActions(),
            BulkAction::make('export')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(fn (Collection $records) => static::exportRecords($records)),
        ]),
    ];
}
```

**Default bulk actions provided:**
- `DeleteBulkAction::make()` - Delete selected records
- `RestoreBulkAction::make()` - Restore selected records (if soft deletes enabled)
- `ForceDeleteBulkAction::make()` - Permanently delete selected (if soft deletes enabled)

### Replacing Default Actions

To completely replace default actions instead of extending them:

```php
protected static function getDefaultRecordActions(): array
{
    return [
        ViewAction::make(),
        Action::make('custom')->icon('heroicon-o-star'),
        // No Edit or Delete actions
    ];
}
```

---

## Soft Deletes

### Enabling Soft Deletes

1. Ensure your model uses `SoftDeletes` trait
2. Enable in the resource:

```php
class UserResource extends BaseResource
{
    protected static ?string $model = User::class;

    protected static bool $supportsSoftDeletes = true;
}
```

### What Changes With Soft Deletes Enabled

**Record Actions:**
- ✅ View
- ✅ Edit
- ✅ Delete (soft delete)
- ✅ **Restore** (new)
- ✅ **Force Delete** (new)

**Bulk Actions:**
- ✅ Delete Bulk (soft delete)
- ✅ **Restore Bulk** (new)
- ✅ **Force Delete Bulk** (new)

**Table Behavior:**
- Deleted records are filtered by default
- Use Filament's built-in trashed filter to show deleted records

---

## Pagination

### Default Configuration

BaseResource applies these pagination settings by default:

```php
->defaultPaginationPageOption(25)
->paginationPageOptions([10, 25, 50, 100])
```

### Customizing Per Resource

```php
class ProductResource extends BaseResource
{
    protected static int $defaultRecordsPerPage = 100;
    protected static array $paginationOptions = [50, 100, 250, 500];
}
```

### Session Persistence

BaseResource enables session persistence for:
- **Filters** - `persistFiltersInSession()`
- **Search** - `persistSearchInSession()`
- **Column Searches** - `persistColumnSearchesInSession()`
- **Sort Order** - `persistSortInSession()`

Users' table state is preserved between page visits.

---

## Global Search

### Configuration

BaseResource provides methods to control global search behavior:

```php
public static function isGloballySearchable(): bool
{
    return true; // Default: enabled
}

public static function getGlobalSearchResultsLimit(): int
{
    return 5; // Default: 5 results
}
```

### Customizing Per Resource

```php
class ProductResource extends BaseResource
{
    public static function isGloballySearchable(): bool
    {
        return true; // Enable for products
    }

    public static function getGlobalSearchResultsLimit(): int
    {
        return 10; // Show 10 products in global search
    }
}
```

### Disabling Global Search

```php
class InternalLogResource extends BaseResource
{
    public static function isGloballySearchable(): bool
    {
        return false; // Exclude from global search
    }
}
```

---

## Migration Guide

### Migrating Existing Resources

**Before (Standard Filament Resource):**

```php
class ShopResource extends Resource
{
    public static function table(Table $table): Table
    {
        return $table
            ->columns([...])
            ->filters([...])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
```

**After (Using BaseResource):**

```php
use App\Filament\BaseResource;

class ShopResource extends BaseResource
{
    public static function table(Table $table): Table
    {
        return static::configureTable($table)
            ->columns([...])
            ->filters([...]);
        // Actions automatically configured!
    }
}
```

### Step-by-Step Migration

1. **Change the parent class:**
   ```php
   - use Filament\Resources\Resource;
   + use App\Filament\BaseResource;

   - class ShopResource extends Resource
   + class ShopResource extends BaseResource
   ```

2. **Update the table method:**
   ```php
   public static function table(Table $table): Table
   {
   -   return $table
   +   return static::configureTable($table)
           ->columns([...])
   -       ->filters([...])
   -       ->recordActions([...])
   -       ->toolbarActions([...]);
   +       ->filters([...]);
   }
   ```

3. **Remove manual action configuration:**
   Delete the `->recordActions()` and `->toolbarActions()` calls

4. **Test the resource:**
   - Verify all actions work
   - Check pagination
   - Test filters and search

---

## Examples

### Example 1: Simple Resource

```php
<?php

namespace App\Filament\Resources;

use App\Filament\BaseResource;
use App\Filament\Resources\DriverResource\Pages;
use App\Models\Driver;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DriverResource extends BaseResource
{
    protected static ?string $model = Driver::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';

    public static function form(Schema $form): Schema
    {
        return $form->components([
            Section::make('Driver Information')->schema([
                TextInput::make('name')->required(),
                TextInput::make('phone')->tel(),
                Toggle::make('is_active')->default(true),
                Toggle::make('is_available')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return static::configureTable($table)
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('phone')->searchable(),
                IconColumn::make('is_active')->boolean(),
                IconColumn::make('is_available')->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active'),
                TernaryFilter::make('is_available'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDrivers::route('/'),
            'create' => Pages\CreateDriver::route('/create'),
            'view' => Pages\ViewDriver::route('/{record}'),
            'edit' => Pages\EditDriver::route('/{record}/edit'),
        ];
    }
}
```

### Example 2: Resource with Soft Deletes

```php
<?php

namespace App\Filament\Resources;

use App\Filament\BaseResource;
use App\Models\User;

class UserResource extends BaseResource
{
    protected static ?string $model = User::class;
    protected static bool $supportsSoftDeletes = true; // Enable soft deletes

    // ... form and table configuration
}
```

### Example 3: Custom Actions

```php
<?php

namespace App\Filament\Resources;

use App\Filament\BaseResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;

class OrderResource extends BaseResource
{
    protected static ?string $model = Order::class;

    protected static function getDefaultRecordActions(): array
    {
        return [
            ...parent::getDefaultRecordActions(),
            Action::make('invoice')
                ->icon('heroicon-o-document-text')
                ->url(fn (Order $record) => route('orders.invoice', $record))
                ->openUrlInNewTab(),
        ];
    }

    protected static function getDefaultToolbarActions(): array
    {
        return [
            BulkActionGroup::make([
                ...parent::getDefaultToolbarActions()[0]->getActions(),
                BulkAction::make('export')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (Collection $records) => static::exportOrders($records)),
            ]),
        ];
    }

    protected static function exportOrders(Collection $orders): void
    {
        // Export logic here
    }
}
```

### Example 4: Large Dataset with Custom Pagination

```php
<?php

namespace App\Filament\Resources;

use App\Filament\BaseResource;
use App\Models\Product;

class ProductResource extends BaseResource
{
    protected static ?string $model = Product::class;

    // Increase default page size for large datasets
    protected static int $defaultRecordsPerPage = 100;

    // Provide more pagination options
    protected static array $paginationOptions = [50, 100, 250, 500];

    // Show more results in global search
    public static function getGlobalSearchResultsLimit(): int
    {
        return 10;
    }

    // ... form and table configuration
}
```

---

## Best Practices

### 1. Always Use configureTable()

❌ **Don't:**
```php
public static function table(Table $table): Table
{
    return $table
        ->columns([...])
        ->recordActions([ViewAction::make(), EditAction::make()]);
}
```

✅ **Do:**
```php
public static function table(Table $table): Table
{
    return static::configureTable($table)
        ->columns([...]);
}
```

### 2. Extend Default Actions, Don't Replace

❌ **Don't:**
```php
protected static function getDefaultRecordActions(): array
{
    return [
        ViewAction::make(),
        Action::make('custom'),
        // Lost EditAction and DeleteAction!
    ];
}
```

✅ **Do:**
```php
protected static function getDefaultRecordActions(): array
{
    return [
        ...parent::getDefaultRecordActions(),
        Action::make('custom'),
    ];
}
```

### 3. Enable Soft Deletes Properly

Ensure your model uses `SoftDeletes` trait before enabling in the resource:

```php
// Model
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use SoftDeletes;
}

// Resource
class UserResource extends BaseResource
{
    protected static bool $supportsSoftDeletes = true;
}
```

### 4. Customize Pagination for Resource Type

- **Small datasets** (< 100 records): 25 per page
- **Medium datasets** (100-1000 records): 50 per page
- **Large datasets** (> 1000 records): 100+ per page

---

## Troubleshooting

### Actions Not Appearing

**Problem:** Record actions or bulk actions don't show up

**Solution:** Ensure you're calling `static::configureTable($table)`:

```php
public static function table(Table $table): Table
{
    return static::configureTable($table) // ← Don't forget this!
        ->columns([...]);
}
```

### Soft Delete Actions Not Working

**Problem:** Restore/Force Delete actions aren't visible

**Solution:** Check two things:
1. Model uses `SoftDeletes` trait
2. Resource has `$supportsSoftDeletes = true`

### Pagination Not Persisting

**Problem:** Pagination settings reset on page refresh

**Solution:** This is expected behavior. Session persistence applies to:
- Filters
- Search queries
- Column searches
- Sort order

**NOT** to pagination page size (Filament limitation).

---

## Summary

BaseResource provides:

✅ **Standard CRUD actions** out of the box
✅ **Soft delete support** with one property
✅ **Bulk actions** for efficient management
✅ **Session persistence** for better UX
✅ **Configurable pagination** per resource
✅ **Global search** integration
✅ **DRY principle** - less code duplication

Use it for all your Filament resources to maintain consistency and reduce boilerplate code!
