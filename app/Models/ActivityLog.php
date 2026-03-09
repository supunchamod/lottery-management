<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id',
        'user_name',
        'action',
        'module',
        'description',
        'old_values',
        'new_values',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Badge colour helper ────────────────────────────────────────────────────

    public function actionBadgeClass(): string
    {
        return match ($this->action) {
            'created'    => 'badge-created',
            'updated'    => 'badge-updated',
            'deleted'    => 'badge-deleted',
            'logged_in'  => 'badge-login',
            default      => 'badge-other',
        };
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            'created'   => 'Created',
            'updated'   => 'Updated',
            'deleted'   => 'Deleted',
            'logged_in' => 'Logged In',
            default     => ucfirst($this->action),
        };
    }

    // ── Static factory ─────────────────────────────────────────────────────────

    /**
     * Record an activity log entry. Called from the LogsActivity trait and
     * any controller that wants manual logging (e.g. auth events).
     */
    public static function record(
        string $action,
        Model  $model,
        array  $oldValues,
        array  $newValues
    ): void {
        // Never log changes to the activity_logs table itself.
        if ($model instanceof self) {
            return;
        }

        $excluded = ['created_at', 'updated_at', 'deleted_at', 'password', 'remember_token'];

        $old = array_diff_key($oldValues, array_flip($excluded));
        $new = array_diff_key($newValues, array_flip($excluded));

        // For updates, skip if nothing meaningful changed.
        if ($action === 'updated' && empty($new)) {
            return;
        }

        $user   = auth()->user();
        $module = self::moduleName($model);
        $ident  = self::identifier($model);

        $description = match ($action) {
            'created' => "Created new {$module}: {$ident}",
            'updated' => "Updated {$module} '{$ident}'" . (
                ! empty($old)
                    ? ': changed ' . implode(', ', array_map(
                        fn ($k) => str_replace('_', ' ', $k),
                        array_keys($old)
                    ))
                    : ''
            ),
            'deleted' => "Deleted {$module}: {$ident}",
            default   => ucfirst($action) . " {$module}: {$ident}",
        };

        static::create([
            'user_id'     => $user?->id,
            'user_name'   => $user?->name ?? 'System',
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
            'old_values'  => $old ?: null,
            'new_values'  => $new ?: null,
            'ip_address'  => request()?->ip(),
        ]);
    }

    // ── Helpers ────────────────────────────────────────────────────────────────

    private static function moduleName(Model $model): string
    {
        $map = [
            'Lottery'             => 'Lottery',
            'LotteryStock'        => 'Stock',
            'Expense'             => 'Expense',
            'Cheque'              => 'Cheque',
            'Winning'             => 'Winning',
            'DailySale'           => 'Daily Sale',
            'DailySaleRecord'     => 'Daily Sale Record',
            'DailyTicketStock'    => 'Daily Ticket Stock',
            'SalesAssistant'      => 'Sales Assistant',
            'SubSeller'           => 'Sub Seller',
            'TicketDistribution'  => 'Ticket Distribution',
            'Ledger'              => 'Ledger',
            'BoardSettlement'     => 'Board Settlement',
            'BoardTransaction'    => 'Board Transaction',
        ];

        $class = class_basename($model);
        return $map[$class] ?? preg_replace('/(?<!^)[A-Z]/', ' $0', $class);
    }

    private static function identifier(Model $model): string
    {
        foreach (['name', 'title', 'cheque_no', 'date', 'description'] as $field) {
            $val = $model->getAttribute($field);
            if (! empty($val)) {
                // Limit long text
                $str = is_object($val) ? $val->format('Y-m-d') : (string) $val;
                return mb_strlen($str) > 60 ? mb_substr($str, 0, 57) . '…' : $str;
            }
        }
        return '#' . $model->getKey();
    }
}
