<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('audit-logs.view');
        $action = trim((string) $request->query('action'));
        $userId = $request->integer('user_id');

        $logs = AuditLog::query()->with('user')
            ->when($action !== '', fn (Builder $query) => $query->where('action', $action))
            ->when($userId > 0, fn (Builder $query) => $query->where('user_id', $userId))
            ->latest('created_at')->paginate(30)->withQueryString();

        return view('audit-logs.index', [
            'logs' => $logs,
            'users' => User::query()->orderBy('name')->get(),
            'actions' => AuditLog::query()->distinct()->orderBy('action')->pluck('action'),
            'selectedAction' => $action,
            'selectedUser' => $userId,
        ]);
    }
}
