<table class='table table-bordered table-striped'>
    <thead>
        <tr>
            <th>No.</th>
            <th>Section / Checklist</th>
            <th width="12%">Location Type</th>
            <th width="16%">Location / Department</th>
            <th>User</th>
            <th width="9%">Time Type</th>
            <th width="7%">Time</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($allSections as $key => $value)
        @php 
            $uniqueId = uniqid() . time();
        @endphp
            <tr>
                <td> {{ $loop->iteration }} </td>
                <td>
                    {{ isset($allSectionLabels[$value]) ? $allSectionLabels[$value] : 'N/A' }} /
                    {{ isset ($allChecklists[$key]) && isset($allChecklistLabels[$allChecklists[$key]]) ? $allChecklistLabels[$allChecklists[$key]] : 'N/A' }}

                    <input type="hidden" name="template_section[{{ $key }}]" value="{{ $value }}">
                    <input type="hidden" name="template_checklist[{{ $key }}]" value="{{ isset($allChecklists[$key]) ? $allChecklists[$key] : null }}">
                    <input type="hidden" name="template_tat[{{ $key }}]" class="template_tat" id="template_tat-{{ $uniqueId }}" data-uid="{{ $uniqueId }}">
                    <input type="hidden" name="template_ctat[{{ $key }}]" class="template_ctat" id="template_ctat-{{ $uniqueId }}" data-uid="{{ $uniqueId }}">
                </td>
                <td>
                    <select name="template_branch_type[{{ $key }}]" class="template_branch_type" id="template_branch_type-{{ $uniqueId }}" data-uid="{{ $uniqueId }}" required>
                        <option value="" selected></option>
                        <option value="1"> Location </option>
                        <option value="2"> Department </option>
                    </select>
                </td>
                <td>
                    <select name="template_branch[{{ $key }}]" class="template_branch" id="template_branch-{{ $uniqueId }}" data-uid="{{ $uniqueId }}" required>
                    </select>                    
                </td>
                <td>
                    <select name="template_user[{{ $key }}]" class="template_user" id="template_user-{{ $uniqueId }}" data-uid="{{ $uniqueId }}" required>
                    </select>                    
                </td>
                <td>
                    <select name="template_time_type[{{ $key }}]" class="template_time_type" id="template_time_type-{{ $uniqueId }}" data-uid="{{ $uniqueId }}" required>
                        <option value="" selected></option>
                        <option value="0"> Minute </option>
                        <option value="1"> Hour </option>
                        <option value="2"> Day </option>
                    </select>
                </td>
                <td>
                    <input type="number" name="template_time[{{ $key }}]" class="form-control" id="template_time-{{ $uniqueId }}" min="1" data-uid="{{ $uniqueId }}" required>
                </td>
                <td>
                    <button type="button" class="btn btn-sm btn-primary w-100" id="template_escalation_btn-{{ $uniqueId }}" data-uid="{{ $uniqueId }}" data-bs-toggle="modal" data-bs-target="#notificationModal"> TAT </button>
                </td>
            </tr>       
        @empty
        @endforelse
    </tbody>
</table>