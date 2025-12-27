# 🚀 PROMPT CLAUDE CODE - PARTIE 9 : NOTIFICATIONS, LISTENERS, PROVIDERS

> **Contexte** : Créer notifications pour UX, listeners pour events, et configurer providers

---

## 📋 OBJECTIF

Créer **8 fichiers** (5 notifications + 1 listener + 2 modifications providers) pour finaliser l'automation.

**Principe** : Notifications pour communication user, Listeners pour cache invalidation, Providers pour configuration.

---

## 🎯 CONTRAINTES STRICTES

### **Architecture**
- ✅ Notifications multi-canaux (mail + database)
- ✅ Listeners découplés
- ✅ Events Laravel standards
- ✅ Providers avec boot ordering

### **UX**
- ✅ Messages clairs et actionnables
- ✅ Links vers actions
- ✅ Formatting professionnel
- ✅ I18n ready (traductions)

### **Code Quality**
- ✅ PHPDoc exhaustif
- ✅ Type hints partout
- ✅ Templates Blade clairs
- ✅ < 150 lignes par fichier

---

## 📁 LISTE DES 8 FICHIERS

### **Notifications (5)**
```
app/Notifications/PermissionExpiredNotification.php
app/Notifications/PermissionExpiringNotification.php
app/Notifications/PermissionDelegatedNotification.php
app/Notifications/DelegationRevokedNotification.php
app/Notifications/PermissionRequestStatusNotification.php
```

### **Listener (1)**
```
app/Listeners/InvalidatePermissionCache.php
```

### **Providers (2 modifications)**
```
app/Providers/AppServiceProvider.php
app/Providers/EventServiceProvider.php
```

---

## 📐 SPÉCIFICATIONS DÉTAILLÉES

### **NOTIFICATION 1 : PermissionExpiredNotification**

**Fichier** : `app/Notifications/PermissionExpiredNotification.php`

```php
<?php

namespace App\Notifications;

use App\Models\Permission;
use App\Models\Scope;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PermissionExpiredNotification
 *
 * Notify user that a permission has expired
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionExpiredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance
     */
    public function __construct(
        public Permission $permission,
        public ?Scope $scope = null
    ) {}

    /**
     * Get the notification's delivery channels
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Permission Expired')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your permission has expired:')
            ->line('**Permission:** ' . $this->permission->name)
            ->line('**Slug:** ' . $this->permission->slug);

        if ($this->scope) {
            $message->line('**Scope:** ' . $this->scope->getDisplayName());
        }

        $message->line('If you need to renew this permission, please contact your administrator.')
            ->action('View My Permissions', url('/my-permissions'))
            ->line('Thank you!');

        return $message;
    }

    /**
     * Get the array representation of the notification
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'permission_id' => $this->permission->id,
            'permission_slug' => $this->permission->slug,
            'permission_name' => $this->permission->name,
            'scope_id' => $this->scope?->id,
            'scope_name' => $this->scope?->getDisplayName(),
            'message' => 'Your permission "' . $this->permission->name . '" has expired.',
        ];
    }
}
```

---

### **NOTIFICATION 2 : PermissionExpiringNotification**

**Fichier** : `app/Notifications/PermissionExpiringNotification.php`

```php
<?php

namespace App\Notifications;

use App\Models\Permission;
use App\Models\Scope;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PermissionExpiringNotification
 *
 * Notify user that a permission will expire soon
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance
     */
    public function __construct(
        public Permission $permission,
        public Carbon $expiresAt,
        public ?Scope $scope = null
    ) {}

    /**
     * Get the notification's delivery channels
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification
     */
    public function toMail(object $notifiable): MailMessage
    {
        $daysRemaining = now()->diffInDays($this->expiresAt);

        $message = (new MailMessage)
            ->subject('Permission Expiring Soon')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your permission will expire in ' . $daysRemaining . ' day(s):')
            ->line('**Permission:** ' . $this->permission->name)
            ->line('**Slug:** ' . $this->permission->slug)
            ->line('**Expires At:** ' . $this->expiresAt->format('Y-m-d H:i'));

        if ($this->scope) {
            $message->line('**Scope:** ' . $this->scope->getDisplayName());
        }

        $message->line('Please contact your administrator if you need to extend this permission.')
            ->action('Request Extension', url('/permission-requests/create'))
            ->line('Thank you!');

        return $message;
    }

    /**
     * Get the array representation of the notification
     */
    public function toArray(object $notifiable): array
    {
        return [
            'permission_id' => $this->permission->id,
            'permission_slug' => $this->permission->slug,
            'permission_name' => $this->permission->name,
            'expires_at' => $this->expiresAt->toDateTimeString(),
            'days_remaining' => now()->diffInDays($this->expiresAt),
            'scope_id' => $this->scope?->id,
            'scope_name' => $this->scope?->getDisplayName(),
            'message' => 'Your permission "' . $this->permission->name . '" expires in ' . now()->diffInDays($this->expiresAt) . ' day(s).',
        ];
    }
}
```

---

### **NOTIFICATION 3 : PermissionDelegatedNotification**

**Fichier** : `app/Notifications/PermissionDelegatedNotification.php`

```php
<?php

namespace App\Notifications;

use App\Models\PermissionDelegation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * PermissionDelegatedNotification
 *
 * Notify user that a permission has been delegated to them
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class PermissionDelegatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance
     */
    public function __construct(
        public PermissionDelegation $delegation
    ) {}

    /**
     * Get the notification's delivery channels
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Permission Delegated to You')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('A permission has been delegated to you:')
            ->line('**Permission:** ' . $this->delegation->permission_slug)
            ->line('**Delegated By:** ' . $this->delegation->delegator_name)
            ->line('**Valid Until:** ' . $this->delegation->valid_until->format('Y-m-d H:i'));

        if ($this->delegation->scope) {
            $message->line('**Scope:** ' . $this->delegation->scope->getDisplayName());
        }

        if ($this->delegation->reason) {
            $message->line('**Reason:** ' . $this->delegation->reason);
        }

        if ($this->delegation->can_redelegate) {
            $message->line('**Note:** You can re-delegate this permission to others.')
                ->action('Manage Delegations', url('/my-delegations'));
        } else {
            $message->action('View My Delegations', url('/my-delegations'));
        }

        $message->line('Thank you!');

        return $message;
    }

    /**
     * Get the array representation of the notification
     */
    public function toArray(object $notifiable): array
    {
        return [
            'delegation_id' => $this->delegation->id,
            'permission_slug' => $this->delegation->permission_slug,
            'delegator_id' => $this->delegation->delegator_id,
            'delegator_name' => $this->delegation->delegator_name,
            'valid_until' => $this->delegation->valid_until->toDateTimeString(),
            'can_redelegate' => $this->delegation->can_redelegate,
            'reason' => $this->delegation->reason,
            'scope_id' => $this->delegation->scope_id,
            'scope_name' => $this->delegation->scope?->getDisplayName(),
            'message' => $this->delegation->delegator_name . ' delegated "' . $this->delegation->permission_slug . '" to you until ' . $this->delegation->valid_until->format('Y-m-d'),
        ];
    }
}
```

---

### **NOTIFICATIONS 4-5** : Structure similaire

**DelegationRevokedNotification.php** (~120 lignes)
**PermissionRequestStatusNotification.php** (~150 lignes)

---

### **LISTENER : InvalidatePermissionCache**

**Fichier** : `app/Listeners/InvalidatePermissionCache.php`

```php
<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\Permissions\PermissionChecker;
use Illuminate\Support\Facades\Log;

/**
 * InvalidatePermissionCache Listener
 *
 * Invalidate permission cache when permissions change
 *
 * @author Noflaye Box Team
 * @version 1.0.0
 */
class InvalidatePermissionCache
{
    /**
     * Create the event listener
     */
    public function __construct(
        private PermissionChecker $permissionChecker
    ) {}

    /**
     * Handle the event
     *
     * @param object $event Event with user property
     */
    public function handle(object $event): void
    {
        if (!isset($event->user) || !$event->user instanceof User) {
            return;
        }

        // Invalidate user permission cache
        $this->permissionChecker->invalidateUserCache($event->user);

        Log::info('Permission cache invalidated', [
            'user_id' => $event->user->id,
            'event' => get_class($event),
        ]);
    }
}
```

---

### **PROVIDER 1 : AppServiceProvider (MODIFICATION)**

**Fichier** : `app/Providers/AppServiceProvider.php`

**Modifications à apporter** :

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Permissions\PermissionChecker;
use App\Services\Permissions\ScopeManager;
use App\Services\Permissions\WildcardExpander;
use App\Services\Permissions\ConditionEvaluator;
use App\Services\Permissions\PermissionAuditLogger;
use App\Services\Permissions\PermissionDelegator;
use App\Services\Permissions\TemplateVersionManager;
use App\Services\Permissions\PermissionAnalytics;
use App\Services\Permissions\PermissionApprovalWorkflow;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services
     */
    public function register(): void
    {
        // Register permission services as singletons
        $this->app->singleton(ScopeManager::class);
        $this->app->singleton(WildcardExpander::class);
        $this->app->singleton(ConditionEvaluator::class);
        $this->app->singleton(PermissionChecker::class);
        $this->app->singleton(PermissionAuditLogger::class);
        $this->app->singleton(PermissionDelegator::class);
        $this->app->singleton(TemplateVersionManager::class);
        $this->app->singleton(PermissionAnalytics::class);
        $this->app->singleton(PermissionApprovalWorkflow::class);
    }

    /**
     * Bootstrap any application services
     */
    public function boot(): void
    {
        // Boot observers
        $this->bootObservers();
    }

    /**
     * Boot model observers
     */
    private function bootObservers(): void
    {
        \App\Models\UserGroup::observe(\App\Observers\UserGroupObserver::class);
        \App\Models\PermissionTemplate::observe(\App\Observers\PermissionTemplateObserver::class);
        \App\Models\PermissionGroup::observe(\App\Observers\PermissionGroupObserver::class);
        \App\Models\Permission::observe(\App\Observers\PermissionObserver::class);
        \App\Models\User::observe(\App\Observers\UserPermissionObserver::class);
    }
}
```

---

### **PROVIDER 2 : EventServiceProvider (MODIFICATION)**

**Fichier** : `app/Providers/EventServiceProvider.php`

**Modifications à apporter** :

```php
<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Permission cache invalidation
        \Illuminate\Database\Events\ModelCreated::class => [
            \App\Listeners\InvalidatePermissionCache::class,
        ],
        \Illuminate\Database\Events\ModelUpdated::class => [
            \App\Listeners\InvalidatePermissionCache::class,
        ],
        \Illuminate\Database\Events\ModelDeleted::class => [
            \App\Listeners\InvalidatePermissionCache::class,
        ],
    ];

    /**
     * Register any events for your application
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
```

---

## ✅ CHECKLIST VALIDATION

Pour chaque notification :

- [ ] PHPDoc complet
- [ ] Implements ShouldQueue
- [ ] via() returns ['mail', 'database']
- [ ] toMail() avec action link
- [ ] toArray() avec toutes données
- [ ] Messages clairs et actionnables

Pour listener :

- [ ] PHPDoc complet
- [ ] Constructor injection
- [ ] handle() type-hinted
- [ ] Logging approprié

Pour providers :

- [ ] Services registered as singletons
- [ ] Observers booted
- [ ] Events mapped to listeners

---

## 🚀 COMMANDE

**Génère les 8 fichiers** :

**5 Notifications** :
```
app/Notifications/PermissionExpiredNotification.php
app/Notifications/PermissionExpiringNotification.php
app/Notifications/PermissionDelegatedNotification.php
app/Notifications/DelegationRevokedNotification.php
app/Notifications/PermissionRequestStatusNotification.php
```

**1 Listener** :
```
app/Listeners/InvalidatePermissionCache.php
```

**2 Providers (modifications)** :
```
app/Providers/AppServiceProvider.php
app/Providers/EventServiceProvider.php
```

**Chaque fichier doit :**
1. Avoir PHPDoc exhaustif
2. Type hints complets
3. Messages clairs
4. Action links
5. Être production-ready

---

**GO ! 🎯**
