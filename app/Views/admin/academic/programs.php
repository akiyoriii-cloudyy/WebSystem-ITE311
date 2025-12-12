<?= $this->include('template/header') ?>

<div class="container mt-4">
    <h2 class="text-center fw-bold mb-4">Programs Management</h2>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>
    <?php if (session()->has('errors')): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach(session('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Add/Edit Program</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= site_url('admin/programs') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" id="action" value="create">
                        <input type="hidden" name="id" id="edit_id">

                        <div class="mb-3">
                            <label class="form-label">Department</label>
                            <select name="department_id" id="department_id" class="form-select" required>
                                <option value="">-- Select Department --</option>
                                <?php foreach ($departments as $dept): ?>
                                    <option value="<?= $dept['id'] ?>"><?= esc($dept['department_code']) ?> - <?= esc($dept['department_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Programs must belong to a department</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Program Code</label>
                            <input type="text" name="program_code" id="program_code" class="form-control" required placeholder="e.g., BSIT, BSCS" maxlength="20">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Program Name</label>
                            <input type="text" name="program_name" id="program_name" class="form-control" required placeholder="e.g., Bachelor of Science in Information Technology">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Optional description"></textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                                <label class="form-check-label" for="is_active">Active</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100" id="submitBtn">Create Program</button>
                        <button type="button" class="btn btn-secondary w-100 mt-2" onclick="resetForm()" id="cancelBtn" style="display:none;">Cancel</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Programs List</h5>
                </div>
                <div class="card-body">
                    <!-- ✅ Search Form for Programs -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <form id="searchProgramsForm" method="GET" action="javascript:void(0);">
                                <div class="row">
                                    <div class="col-md-10">
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                                                </svg>
                                            </span>
                                            <input 
                                                type="text" 
                                                class="form-control" 
                                                id="searchProgramsInput" 
                                                name="q" 
                                                placeholder="Search programs by code, name, department, or description..." 
                                                value=""
                                                autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100" id="searchProgramsBtn">
                                            Search
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- ✅ Search Results Info -->
                    <div id="searchProgramsInfo" class="mb-3" style="display: none;">
                        <p class="text-muted">
                            <span id="programsResultCount">0</span> result(s) found
                        </p>
                    </div>

                    <table class="table table-bordered" id="programsTable">
                        <thead>
                            <tr>
                                <th>Department</th>
                                <th>Program Code</th>
                                <th>Program Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="programsTableBody">
                            <?php if (!empty($programs)): ?>
                                <?php foreach ($programs as $program): ?>
                                    <tr>
                                        <td><strong><?= esc($program['department_code']) ?></strong><br><small><?= esc($program['department_name']) ?></small></td>
                                        <td><strong><?= esc($program['program_code']) ?></strong></td>
                                        <td><?= esc($program['program_name']) ?></td>
                                        <td><?= esc($program['description'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if ($program['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick="editProgram(<?= htmlspecialchars(json_encode($program)) ?>)">Edit</button>
                                            <form method="post" action="<?= site_url('admin/programs') ?>" style="display:inline;" onsubmit="return confirm('Delete this program?')">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $program['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No programs found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <div id="noProgramsSearchResults" style="display: none;">
                        <p class="text-muted mb-0 text-center">No programs found matching your search.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editProgram(program) {
    document.getElementById('action').value = 'update';
    document.getElementById('edit_id').value = program.id;
    document.getElementById('department_id').value = program.department_id;
    document.getElementById('program_code').value = program.program_code;
    document.getElementById('program_name').value = program.program_name;
    document.getElementById('description').value = program.description || '';
    document.getElementById('is_active').checked = program.is_active == 1;
    document.getElementById('submitBtn').textContent = 'Update Program';
    document.getElementById('cancelBtn').style.display = 'block';
    document.getElementById('program_code').scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('action').value = 'create';
    document.getElementById('edit_id').value = '';
    document.getElementById('department_id').value = '';
    document.getElementById('program_code').value = '';
    document.getElementById('program_name').value = '';
    document.getElementById('description').value = '';
    document.getElementById('is_active').checked = true;
    document.getElementById('submitBtn').textContent = 'Create Program';
    document.getElementById('cancelBtn').style.display = 'none';
}
</script>

<?= $this->include('template/footer') ?>

<script>
// ✅ PROGRAMS SEARCH FUNCTIONALITY - Updated to match working enrollment search pattern
// Moved after footer to ensure jQuery is loaded
$(document).ready(function() {
    let programsSearchTimeout;
    let isSearchingPrograms = false;
    const originalProgramsHtml = $('#programsTableBody').html(); // Store original programs
    
    function performProgramsSearch(searchTerm) {
        if (isSearchingPrograms) return; // Prevent multiple simultaneous searches
        
        isSearchingPrograms = true;
        const $searchBtn = $('#searchProgramsBtn');
        const originalBtnText = $searchBtn.html();
        
        // Show loading indicator
        $searchBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Searching...');

        $.ajax({
            url: "<?= base_url('admin/search/programs') ?>",
            type: "GET",
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                q: searchTerm
            },
            dataType: 'json',
            success: function(response) {
                isSearchingPrograms = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);

                console.log('Program search response:', response); // Debug log

                if (response.status === 'success') {
                    // Update search info
                    $('#programsResultCount').text(response.count);
                    $('#searchProgramsInfo').show();

                    // Clear existing programs
                    $('#programsTableBody').empty();
                    $('#noProgramsSearchResults').hide();

                    if (response.results && response.results.length > 0) {
                        // Build table rows from search results
                        response.results.forEach(function(program, index) {
                            const statusBadge = program.is_active 
                                ? '<span class="badge bg-success">Active</span>'
                                : '<span class="badge bg-secondary">Inactive</span>';
                            
                            // Use data attributes instead of inline onclick for better reliability
                            const row = '<tr>' +
                                '<td><strong>' + (program.department_code || 'N/A') + '</strong><br><small>' + (program.department_name || '') + '</small></td>' +
                                '<td><strong>' + (program.program_code || 'N/A') + '</strong></td>' +
                                '<td>' + (program.program_name || 'N/A') + '</td>' +
                                '<td>' + (program.description || 'N/A') + '</td>' +
                                '<td>' + statusBadge + '</td>' +
                                '<td>' +
                                '<button class="btn btn-sm btn-warning edit-prog-btn" ' +
                                'data-prog-id="' + program.id + '" ' +
                                'data-prog-dept-id="' + (program.department_id || '') + '" ' +
                                'data-prog-code="' + (program.program_code || '').replace(/"/g, '&quot;') + '" ' +
                                'data-prog-name="' + (program.program_name || '').replace(/"/g, '&quot;') + '" ' +
                                'data-prog-desc="' + (program.description || '').replace(/"/g, '&quot;').replace(/\n/g, ' ') + '" ' +
                                'data-prog-active="' + (program.is_active ? 1 : 0) + '">Edit</button> ' +
                                '<form method="post" action="<?= site_url("admin/programs") ?>" style="display:inline;" onsubmit="return confirm(\'Delete this program?\')">' +
                                '<input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">' +
                                '<input type="hidden" name="action" value="delete">' +
                                '<input type="hidden" name="id" value="' + program.id + '">' +
                                '<button type="submit" class="btn btn-sm btn-danger">Delete</button>' +
                                '</form>' +
                                '</td>' +
                            '</tr>';
                            $('#programsTableBody').append(row);
                        });
                    } else {
                        $('#noProgramsSearchResults').show();
                    }
                } else {
                    $('#searchProgramsInfo').hide();
                    alert('Search failed: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                isSearchingPrograms = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);
                $('#searchProgramsInfo').hide();
                console.error('Search error:', error);
                alert('An error occurred during search. Please try again.');
            }
        });
    }

    $('#searchProgramsInput').on('input', function() {
        const searchTerm = $(this).val().trim();
        clearTimeout(programsSearchTimeout);
        
        if (searchTerm === '') {
            clearTimeout(programsSearchTimeout);
            programsSearchTimeout = setTimeout(function() {
                $('#programsTableBody').html(originalProgramsHtml);
                $('#searchProgramsInfo').hide();
                $('#noProgramsSearchResults').hide();
            }, 300);
        } else {
            programsSearchTimeout = setTimeout(function() {
                performProgramsSearch(searchTerm);
            }, 500);
        }
    });

    // Submit form for programs search
    $('#searchProgramsForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear any pending timeout
        clearTimeout(programsSearchTimeout);
        
        // Perform search immediately when form is submitted
        const searchTerm = $('#searchProgramsInput').val().trim();
        if (searchTerm === '') {
            $('#programsTableBody').html(originalProgramsHtml);
            $('#searchProgramsInfo').hide();
            $('#noProgramsSearchResults').hide();
        } else {
            performProgramsSearch(searchTerm);
        }
    });

    // Also handle search button click directly
    $('#searchProgramsBtn').on('click', function(e) {
        e.preventDefault();
        $('#searchProgramsForm').submit();
    });

    // Handle edit button clicks from search results using event delegation
    $(document).on('click', '.edit-prog-btn', function() {
        const progData = {
            id: $(this).data('prog-id'),
            department_id: $(this).data('prog-dept-id') || '',
            program_code: $(this).data('prog-code') || '',
            program_name: $(this).data('prog-name') || '',
            description: $(this).data('prog-desc') || '',
            is_active: $(this).data('prog-active') ? 1 : 0
        };
        editProgram(progData);
    });
});
</script>

