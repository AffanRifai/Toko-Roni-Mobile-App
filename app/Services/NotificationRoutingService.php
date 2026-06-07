<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class NotificationRoutingService
{
    /**
     * Kirim notifikasi dengan routing berbasis role + prioritas.
     *
     * @param  array<string, mixed>  $context
     * @return Collection<int, User>
     */
    public function send(string $eventKey, Notification $notification, ?User $actor = null, array $context = []): Collection
    {
        $recipients = $this->resolveRecipients($eventKey, $actor, $context);

        foreach ($recipients as $recipient) {
            try {
                $recipient->notify($notification);
            } catch (\Throwable $e) {
                Log::warning('Notification send failed', [
                    'event_key' => $eventKey,
                    'recipient_id' => $recipient->id ?? null,
                    'message' => $e->getMessage(),
                ]);
            }
        }

        return $recipients;
    }

    /**
     * Metadata event untuk payload notifikasi (priority, important, dll).
     *
     * @param  array<string, mixed>  $override
     * @return array<string, mixed>
     */
    public function metaForEvent(string $eventKey, array $override = []): array
    {
        $rule = $this->resolveRule($eventKey);
        $priority = $this->normalizePriority((string) ($override['priority'] ?? $rule['priority'] ?? 'normal'));
        $targetRoles = $this->normalizeRoles(array_merge(
            (array) ($rule['roles'] ?? []),
            (array) ($override['roles'] ?? []),
        ));

        return array_merge([
            'event_key' => $eventKey,
            'priority' => $priority,
            'is_important' => in_array($priority, ['high', 'critical'], true),
            'monitor' => (bool) ($override['monitor'] ?? $rule['monitor'] ?? false),
            'target_roles' => $targetRoles,
            'notification_scope' => in_array($priority, ['high', 'critical'], true)
                ? 'priority_notification'
                : 'role_based_notification',
        ], $override);
    }

    /**
     * @param  array<string, mixed>  $context
     * @return Collection<int, User>
     */
    private function resolveRecipients(string $eventKey, ?User $actor, array $context): Collection
    {
        $rule = $this->resolveRule($eventKey);

        $includeSameRole = (bool) ($context['include_same_role'] ?? $rule['include_same_role'] ?? false);
        $includeActor = (bool) ($context['include_actor'] ?? $rule['include_actor'] ?? false);
        $monitor = (bool) ($context['monitor'] ?? $rule['monitor'] ?? false);
        $targetUserIds = array_map('intval', (array) ($context['target_user_ids'] ?? []));
        $excludeUserIds = array_map('intval', (array) ($context['exclude_user_ids'] ?? []));

        $roles = array_merge(
            (array) ($rule['roles'] ?? []),
            (array) ($context['roles'] ?? []),
            (array) ($context['extra_roles'] ?? []),
        );

        if ($includeSameRole && $actor) {
            $roles[] = $actor->role;
        }

        if ($monitor) {
            $roles = array_merge($roles, (array) config('notification_routing.monitor_roles', []));
        }

        $roles = $this->expandRoleTargets($this->normalizeRoles($roles));
        $users = collect();

        if (!empty($roles)) {
            $byRole = User::query()
                ->when($this->supportsUserActiveColumn(), function ($query) {
                    $query->where('is_active', true);
                })
                ->where(function ($query) use ($roles) {
                    foreach ($roles as $role) {
                        $query->orWhereRaw('LOWER(role) = ?', [$role]);
                    }
                })
                ->get();
            $users = $users->concat($byRole);
        }

        if (!empty($targetUserIds)) {
            $byId = User::query()
                ->when($this->supportsUserActiveColumn(), function ($query) {
                    $query->where('is_active', true);
                })
                ->whereIn('id', $targetUserIds)
                ->get();
            $users = $users->concat($byId);
        }

        if ($includeActor && $actor) {
            $users = $users->push($actor);
        }

        return $users
            ->filter(fn ($user) => $user instanceof User && !in_array((int) $user->id, $excludeUserIds, true))
            ->keyBy(fn (User $user) => (int) $user->id)
            ->values();
    }

    /**
     * @return array<string, mixed>
     */
    private function resolveRule(string $eventKey): array
    {
        $events = (array) config('notification_routing.events', []);
        $rule = $events[$eventKey] ?? [];

        return array_merge([
            'roles' => [],
            'include_same_role' => false,
            'include_actor' => false,
            'monitor' => false,
            'priority' => 'normal',
        ], is_array($rule) ? $rule : []);
    }

    /**
     * @param  array<int, string|null>  $roles
     * @return array<int, string>
     */
    private function normalizeRoles(array $roles): array
    {
        $aliases = array_change_key_case((array) config('notification_routing.role_aliases', []), CASE_LOWER);
        $normalized = [];

        foreach ($roles as $role) {
            $raw = strtolower(trim((string) $role));
            if ($raw === '') {
                continue;
            }
            $normalized[] = $aliases[$raw] ?? $raw;
        }

        return array_values(array_unique($normalized));
    }

    /**
     * Expand role target agar alias di database tetap terjangkau.
     *
     * @param  array<int, string>  $roles
     * @return array<int, string>
     */
    private function expandRoleTargets(array $roles): array
    {
        $aliases = array_change_key_case((array) config('notification_routing.role_aliases', []), CASE_LOWER);
        $expanded = [];

        foreach ($roles as $role) {
            $canonical = strtolower(trim($role));
            if ($canonical === '') {
                continue;
            }

            $expanded[] = $canonical;
            foreach ($aliases as $alias => $mapped) {
                if (strtolower(trim((string) $mapped)) === $canonical) {
                    $expanded[] = strtolower(trim((string) $alias));
                }
            }
        }

        return array_values(array_unique($expanded));
    }

    private function normalizePriority(string $priority): string
    {
        $value = strtolower(trim($priority));
        if (in_array($value, ['low', 'normal', 'high', 'critical'], true)) {
            return $value;
        }
        return 'normal';
    }

    private function supportsUserActiveColumn(): bool
    {
        static $checked = null;
        if ($checked !== null) {
            return $checked;
        }
        $checked = Schema::hasColumn('users', 'is_active');
        return $checked;
    }
}
