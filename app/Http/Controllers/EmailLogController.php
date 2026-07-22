<?php

namespace App\Http\Controllers;

use App\Models\EmailLog;
use App\Models\User;
use Illuminate\View\View;

class EmailLogController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $logs = EmailLog::with('user')
            ->latest()
            ->paginate(10);

        return view('email_logs.index', compact('logs'));
    }
}
