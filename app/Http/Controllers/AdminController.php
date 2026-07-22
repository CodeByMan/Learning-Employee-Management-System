<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\EmailLog;
use App\Models\Learner;
use App\Models\LearnerAttendance;
use App\Models\User;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        return view('admin.dashboard', [
            'userCount' => User::count(),
            'learnerCount' => Learner::count(),
            'employeeCount' => User::role(User::ROLE_EMPLOYEE)->count(),
            'mailLogCount' => EmailLog::count(),
            'announcementCount' => Announcement::count(),
            'attendanceCount' => LearnerAttendance::count(),
        ]);
    }
}
