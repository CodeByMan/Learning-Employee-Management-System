<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LookupLearnerRequest;
use App\Http\Requests\Admin\StoreAttendanceRequest;
use App\Models\Learner;
use App\Models\LearnerAttendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LearnerAttendanceController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Learner::class);

        $today = today()->toDateString();
        $learners = Learner::query()
            ->orderBy('lname')
            ->orderBy('fname')
            ->get(['id', 'fname', 'lname']);

        $attendances = LearnerAttendance::with('learner')
            ->whereDate('date', $today)
            ->latest('updated_at')
            ->paginate(10);

        return view('admin.attendance.index', compact('learners', 'attendances', 'today'));
    }

    public function lookupLearner(LookupLearnerRequest $request): JsonResponse
    {
        $learner = Learner::where('qr_code', $request->validated('qr_code'))->first();

        if (! $learner) {
            return response()->json(['status' => 'not_found'], 404);
        }

        return response()->json([
            'status' => 'found',
            'learner' => [
                'id' => $learner->id,
                'name' => "{$learner->lname}, {$learner->fname}",
            ],
        ]);
    }

    public function store(StoreAttendanceRequest $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validated();
        $learnerId = $validated['learner_id'] ?? Learner::where('qr_code', $validated['qr_code'])->value('id');

        if (! $learnerId) {
            return $this->attendanceResponse($request, 'warning', 'Invalid or unregistered QR code.', 422);
        }

        $status = DB::transaction(function () use ($learnerId, $validated): string {
            $today = today()->toDateString();

            $attendance = LearnerAttendance::query()
                ->whereDate('date', $today)
                ->firstOrCreate(
                    ['learner_id' => $learnerId],
                    ['date' => $today]
                );

            $attendance = LearnerAttendance::query()
                ->whereKey($attendance->id)
                ->lockForUpdate()
                ->firstOrFail();

            $session = $validated['session'];

            if ($attendance->{$session} !== null) {
                return 'duplicate';
            }

            $attendance->{$session} = now()->format('H:i:s');
            $attendance->save();

            return 'saved';
        });

        if ($status === 'duplicate') {
            return $this->attendanceResponse($request, 'warning', 'This attendance session is already logged.', 200);
        }

        return $this->attendanceResponse($request, 'success', 'Attendance logged successfully.', 200);
    }

    private function attendanceResponse(
        StoreAttendanceRequest $request,
        string $status,
        string $message,
        int $httpStatus
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            return response()->json(compact('status', 'message'), $httpStatus);
        }

        return back()->with($status, $message);
    }
}
