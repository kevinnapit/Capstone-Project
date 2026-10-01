<?php

namespace App\Observers;

use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class MasterDataAuditObserver
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function created(Model $model): void
    {
        $this->write('created', $model, null, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        $before = array_intersect_key($model->getOriginal(), $changes);
        $this->write('updated', $model, $before, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->write('deleted', $model, $model->getOriginal(), null);
    }

    private function write(string $event, Model $model, ?array $before, ?array $after): void
    {
        if (! Auth::check()) {
            return;
        }

        $hidden = array_flip(['password', 'remember_token']);
        $before = $before === null ? null : array_diff_key($before, $hidden);
        $after = $after === null ? null : array_diff_key($after, $hidden);
        $name = class_basename($model);

        $this->audit->log(Auth::user(), "master.{$event}", $model, $before, $after, "{$name} {$event}.");
    }
}
