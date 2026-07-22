<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\StoreLearnerRequest;
use App\Http\Requests\Admin\UpdateLearnerRequest;
use App\Models\Learner;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LearnerController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Learner::class);

        $learners = Learner::query()
            ->orderBy('lname')
            ->orderBy('fname')
            ->orderBy('mname')
            ->paginate(10);

        return view('admin.learners.index', compact('learners'));
    }

    public function store(StoreLearnerRequest $request): RedirectResponse
    {
        Learner::create($request->validated());

        return back()->with('success', 'Learner added successfully.');
    }

    public function update(UpdateLearnerRequest $request, Learner $learner): RedirectResponse
    {
        $learner->update($request->validated());

        return back()->with('success', 'Learner updated successfully.');
    }

    public function destroy(Learner $learner): RedirectResponse
    {
        $this->authorize('delete', $learner);
        $learner->delete();

        return back()->with('success', 'Learner deleted successfully.');
    }

    public function dashboard(): View
    {
        return view('learner.dashboard');
    }
}
