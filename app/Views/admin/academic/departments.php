<?= $this->include('template/header') ?>

<div class="container mt-4">
    <h2 class="text-center fw-bold mb-4">Departments Management</h2>

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
                    <h5 class="mb-0">Add/Edit Department</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= site_url('admin/departments') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" id="action" value="create">
                        <input type="hidden" name="id" id="edit_id">

                        <div class="mb-3">
                            <label class="form-label">Department Code</label>
                            <input type="text" name="department_code" id="department_code" class="form-control" required placeholder="e.g., CS, IT, ENG" maxlength="20">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Department Name</label>
                            <input type="text" name="department_name" id="department_name" class="form-control" required placeholder="e.g., Computer Science">
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

                        <button type="submit" class="btn btn-success w-100" id="submitBtn">Create Department</button>
                        <button type="button" class="btn btn-secondary w-100 mt-2" onclick="resetForm()" id="cancelBtn" style="display:none;">Cancel</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Departments List</h5>
                </div>
                <div class="card-body">
                    <!-- ✅ Search Form for Departments -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <form id="searchDepartmentsForm" method="GET" action="javascript:void(0);">
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
                                                id="searchDepartmentsInput" 
                                                name="q" 
                                                placeholder="Search departments by code, name, or description..." 
                                                value=""
                                                autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100" id="searchDepartmentsBtn">
                                            Search
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- ✅ Search Results Info -->
                    <div id="searchDepartmentsInfo" class="mb-3" style="display: none;">
                        <p class="text-muted">
                            <span id="departmentsResultCount">0</span> result(s) found
                        </p>
                    </div>

                    <table class="table table-bordered" id="departmentsTable">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Department Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="departmentsTableBody">
                            <?php if (!empty($departments)): ?>
                                <?php foreach ($departments as $dept): ?>
                                    <tr>
                                        <td><strong><?= esc($dept['department_code']) ?></strong></td>
                                        <td><?= esc($dept['department_name']) ?></td>
                                        <td><?= esc($dept['description'] ?? 'N/A') ?></td>
                                        <td>
                                            <?php if ($dept['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" onclick="editDepartment(<?= htmlspecialchars(json_encode($dept)) ?>)">Edit</button>
                                            <form method="post" action="<?= site_url('admin/departments') ?>" style="display:inline;" onsubmit="return confirm('Delete this department? This will also delete all associated programs.')">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="id" value="<?= $dept['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No departments found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                    <div id="noDepartmentsSearchResults" style="display: none;">
                        <p class="text-muted mb-0 text-center">No departments found matching your search.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function editDepartment(dept) {
    document.getElementById('action').value = 'update';
    document.getElementById('edit_id').value = dept.id;
    document.getElementById('department_code').value = dept.department_code;
    document.getElementById('department_name').value = dept.department_name;
    document.getElementById('description').value = dept.description || '';
    document.getElementById('is_active').checked = dept.is_active == 1;
    document.getElementById('submitBtn').textContent = 'Update Department';
    document.getElementById('cancelBtn').style.display = 'block';
    document.getElementById('department_code').scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('action').value = 'create';
    document.getElementById('edit_id').value = '';
    document.getElementById('department_code').value = '';
    document.getElementById('department_name').value = '';
    document.getElementById('description').value = '';
    document.getElementById('is_active').checked = true;
    document.getElementById('submitBtn').textContent = 'Create Department';
    document.getElementById('cancelBtn').style.display = 'none';
}
</script>

<?= $this->include('template/footer') ?>

<script>
// ✅ DEPARTMENTS SEARCH FUNCTIONALITY - Updated to match working enrollment search pattern
// Moved after footer to ensure jQuery is loaded
$(document).ready(function() {
    let departmentsSearchTimeout;
    let isSearchingDepartments = false;
    const originalDepartmentsHtml = $('#departmentsTableBody').html(); // Store original departments
    
    function performDepartmentsSearch(searchTerm) {
        if (isSearchingDepartments) return; // Prevent multiple simultaneous searches
        
        isSearchingDepartments = true;
        const $searchBtn = $('#searchDepartmentsBtn');
        const originalBtnText = $searchBtn.html();
        
        // Show loading indicator
        $searchBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Searching...');

        $.ajax({
            url: "<?= base_url('admin/search/departments') ?>",
            type: "GET",
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                q: searchTerm
            },
            dataType: 'json',
            success: function(response) {
                isSearchingDepartments = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);

                console.log('Department search response:', response); // Debug log

                if (response.status === 'success') {
                    // Update search info
                    $('#departmentsResultCount').text(response.count);
                    $('#searchDepartmentsInfo').show();

                    // Clear existing departments
                    $('#departmentsTableBody').empty();
                    $('#noDepartmentsSearchResults').hide();

                    if (response.results && response.results.length > 0) {
                        // Build table rows from search results
                        response.results.forEach(function(dept, index) {
                            const statusBadge = dept.is_active 
                                ? '<span class="badge bg-success">Active</span>'
                                : '<span class="badge bg-secondary">Inactive</span>';
                            
                            // Use data attributes instead of inline onclick for better reliability
                            const row = '<tr>' +
                                '<td><strong>' + (dept.department_code || 'N/A') + '</strong></td>' +
                                '<td>' + (dept.department_name || 'N/A') + '</td>' +
                                '<td>' + (dept.description || 'N/A') + '</td>' +
                                '<td>' + statusBadge + '</td>' +
                                '<td>' +
                                '<button class="btn btn-sm btn-warning edit-dept-btn" ' +
                                'data-dept-id="' + dept.id + '" ' +
                                'data-dept-code="' + (dept.department_code || '').replace(/"/g, '&quot;') + '" ' +
                                'data-dept-name="' + (dept.department_name || '').replace(/"/g, '&quot;') + '" ' +
                                'data-dept-desc="' + (dept.description || '').replace(/"/g, '&quot;').replace(/\n/g, ' ') + '" ' +
                                'data-dept-active="' + (dept.is_active ? 1 : 0) + '">Edit</button> ' +
                                '<form method="post" action="<?= site_url("admin/departments") ?>" style="display:inline;" onsubmit="return confirm(\'Delete this department?\')">' +
                                '<input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">' +
                                '<input type="hidden" name="action" value="delete">' +
                                '<input type="hidden" name="id" value="' + dept.id + '">' +
                                '<button type="submit" class="btn btn-sm btn-danger">Delete</button>' +
                                '</form>' +
                                '</td>' +
                            '</tr>';
                            $('#departmentsTableBody').append(row);
                        });
                    } else {
                        $('#noDepartmentsSearchResults').show();
                    }
                } else {
                    $('#searchDepartmentsInfo').hide();
                    alert('Search failed: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                isSearchingDepartments = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);
                $('#searchDepartmentsInfo').hide();
                console.error('Search error:', error);
                alert('An error occurred during search. Please try again.');
            }
        });
    }

    $('#searchDepartmentsInput').on('input', function() {
        const searchTerm = $(this).val().trim();
        clearTimeout(departmentsSearchTimeout);
        
        if (searchTerm === '') {
            clearTimeout(departmentsSearchTimeout);
            departmentsSearchTimeout = setTimeout(function() {
                $('#departmentsTableBody').html(originalDepartmentsHtml);
                $('#searchDepartmentsInfo').hide();
                $('#noDepartmentsSearchResults').hide();
            }, 300);
        } else {
            departmentsSearchTimeout = setTimeout(function() {
                performDepartmentsSearch(searchTerm);
            }, 500);
        }
    });

    // Submit form for departments search
    $('#searchDepartmentsForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear any pending timeout
        clearTimeout(departmentsSearchTimeout);
        
        // Perform search immediately when form is submitted
        const searchTerm = $('#searchDepartmentsInput').val().trim();
        if (searchTerm === '') {
            $('#departmentsTableBody').html(originalDepartmentsHtml);
            $('#searchDepartmentsInfo').hide();
            $('#noDepartmentsSearchResults').hide();
        } else {
            performDepartmentsSearch(searchTerm);
        }
    });

    // Also handle search button click directly
    $('#searchDepartmentsBtn').on('click', function(e) {
        e.preventDefault();
        $('#searchDepartmentsForm').submit();
    });

    // Handle edit button clicks from search results using event delegation
    $(document).on('click', '.edit-dept-btn', function() {
        const deptData = {
            id: $(this).data('dept-id'),
            department_code: $(this).data('dept-code') || '',
            department_name: $(this).data('dept-name') || '',
            description: $(this).data('dept-desc') || '',
            is_active: $(this).data('dept-active') ? 1 : 0
        };
        editDepartment(deptData);
    });
});
</script>

