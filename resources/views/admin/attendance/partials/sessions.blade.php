<fieldset>
    <legend class="form-label fs-6">Session</legend>
    <div class="d-flex flex-wrap gap-3">
        @foreach (['am_in' => 'AM In', 'am_out' => 'AM Out', 'pm_in' => 'PM In', 'pm_out' => 'PM Out'] as $value => $label)
            <label class="form-check">
                <input class="form-check-input" type="radio" name="session" value="{{ $value }}" @checked($loop->first)>
                <span class="form-check-label">{{ $label }}</span>
            </label>
        @endforeach
    </div>
</fieldset>
