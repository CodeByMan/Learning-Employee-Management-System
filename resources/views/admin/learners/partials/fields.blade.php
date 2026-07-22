<div class="row g-3">
    <div class="col-md-4">
        <label class="form-label">First Name</label>
        <input type="text" name="fname" class="form-control" value="{{ old('fname', $learner?->fname) }}" required maxlength="100">
    </div>
    <div class="col-md-4">
        <label class="form-label">Middle Name</label>
        <input type="text" name="mname" class="form-control" value="{{ old('mname', $learner?->mname) }}" maxlength="100">
    </div>
    <div class="col-md-4">
        <label class="form-label">Last Name</label>
        <input type="text" name="lname" class="form-control" value="{{ old('lname', $learner?->lname) }}" required maxlength="100">
    </div>
    <div class="col-md-6">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" value="{{ old('email', $learner?->email) }}" required maxlength="255">
    </div>
    <div class="col-md-3">
        <label class="form-label">Grade Level</label>
        <select name="grade_level" class="form-select" required>
            <option value="">Select grade</option>
            @foreach (['1st Year', '2nd Year', '3rd Year', '4th Year'] as $grade)
                <option value="{{ $grade }}" @selected(old('grade_level', $learner?->grade_level) === $grade)>{{ $grade }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Section</label>
        <select name="section" class="form-select" required>
            <option value="">Select section</option>
            @foreach (['A', 'B', 'C', 'D'] as $section)
                <option value="{{ $section }}" @selected(old('section', $learner?->section) === $section)>{{ $section }}</option>
            @endforeach
        </select>
    </div>
</div>
