<?= $this->include('template/header') ?>

<div class="container mt-4">
    <h2 class="text-center fw-bold mb-4">Assignments Management - <?= esc($course['title']) ?></h2>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Create Assignment</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= site_url('teacher/courses/' . $course['id'] . '/assignments') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="create">

                        <div class="mb-3">
                            <label class="form-label">Grading Period</label>
                            <select name="grading_period_id" class="form-select" required>
                                <option value="">-- Select Grading Period --</option>
                                <?php foreach ($grading_periods as $period): ?>
                                    <option value="<?= $period['id'] ?>"><?= esc($period['period_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Assignment Type</label>
                            <select name="assignment_type" class="form-select" required>
                                <option value="">-- Select Type --</option>
                                <option value="Quiz">Quiz</option>
                                <option value="Exam">Exam</option>
                                <option value="Project">Project</option>
                                <option value="Lab">Lab</option>
                                <option value="Homework">Homework</option>
                                <option value="Participation">Participation</option>
                                <option value="Final Exam">Final Exam</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Max Score</label>
                            <input type="number" name="max_score" class="form-control" step="0.01" value="100" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Due Date</label>
                            <input type="datetime-local" name="due_date" class="form-control">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Create Assignment</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Assignments List</h5>
                </div>
                <div class="card-body">
                    <!-- ✅ Search Form for Assignments -->
                    <div class="card mb-3">
                        <div class="card-body">
                            <form id="searchCourseAssignmentsForm" method="GET" action="javascript:void(0);">
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
                                                id="searchCourseAssignmentsInput" 
                                                name="q" 
                                                placeholder="Search assignments by type, title, or due date..." 
                                                value=""
                                                autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-primary w-100" id="searchCourseAssignmentsBtn">
                                            Search
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- ✅ Search Results Info -->
                    <div id="searchCourseAssignmentsInfo" class="mb-3" style="display: none;">
                        <p class="text-muted">
                            <span id="courseAssignmentsResultCount">0</span> result(s) found
                        </p>
                    </div>

                    <?php if (!empty($assignments)): ?>
                        <table class="table table-bordered" id="courseAssignmentsTable">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Title</th>
                                    <th>Max Score</th>
                                    <th>Due Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="courseAssignmentsTableBody">
                                <?php foreach ($assignments as $assignment): ?>
                                    <tr>
                                        <td><span class="badge bg-info"><?= esc($assignment['assignment_type']) ?></span></td>
                                        <td><?= esc($assignment['title']) ?></td>
                                        <td><?= number_format($assignment['max_score'], 2) ?></td>
                                        <td><?= $assignment['due_date'] ? date('M d, Y H:i', strtotime($assignment['due_date'])) : 'N/A' ?></td>
                                        <td>
                                            <a href="<?= site_url('teacher/assignments/' . $assignment['id'] . '/grade') ?>" class="btn btn-sm btn-success">Grade</a>
                                            <a href="<?= site_url('teacher/assignments/' . $assignment['id'] . '/delete') ?>" 
                                               class="btn btn-sm btn-danger"
                                               onclick="return confirm('Are you sure you want to delete this assignment? This action cannot be undone.')">
                                                Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div id="noCourseAssignmentsSearchResults" style="display: none;">
                            <p class="text-muted mb-0 text-center">No assignments found matching your search.</p>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">No assignments created yet.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // ✅ COURSE ASSIGNMENTS SEARCH FUNCTIONALITY
    let courseAssignmentsSearchTimeout;
    let isSearchingCourseAssignments = false;
    const originalCourseAssignmentsHtml = $('#courseAssignmentsTableBody').html(); // Store original assignments
    const courseId = <?= $course['id'] ?? 0 ?>;
    
    function performCourseAssignmentsSearch(searchTerm) {
        if (isSearchingCourseAssignments) return;
        
        isSearchingCourseAssignments = true;
        const $searchBtn = $('#searchCourseAssignmentsBtn');
        const originalBtnText = $searchBtn.html();
        
        $searchBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Searching...');

        $.ajax({
            url: "<?= base_url('teacher/search/course-assignments') ?>",
            type: "GET",
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                q: searchTerm,
                course_id: courseId
            },
            dataType: 'json',
            success: function(response) {
                isSearchingCourseAssignments = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);

                if (response.status === 'success') {
                    $('#courseAssignmentsResultCount').text(response.count);
                    $('#searchCourseAssignmentsInfo').show();
                    $('#courseAssignmentsTableBody').empty();
                    $('#noCourseAssignmentsSearchResults').hide();

                    if (response.results.length > 0) {
                        response.results.forEach(function(assignment, index) {
                            const dueDateHtml = assignment.due_date 
                                ? new Date(assignment.due_date).toLocaleString('en-US', {year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'})
                                : 'N/A';
                            
                            const row = '<tr>' +
                                '<td><span class="badge bg-info">' + (assignment.assignment_type || 'N/A') + '</span></td>' +
                                '<td>' + (assignment.title || 'N/A') + '</td>' +
                                '<td>' + parseFloat(assignment.max_score || 0).toFixed(2) + '</td>' +
                                '<td>' + dueDateHtml + '</td>' +
                                '<td>' +
                                '<a href="<?= site_url("teacher/assignments/") ?>' + assignment.id + '/grade" class="btn btn-sm btn-success">Grade</a> ' +
                                '<a href="<?= site_url("teacher/assignments/") ?>' + assignment.id + '/delete" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure you want to delete this assignment?\')">Delete</a>' +
                                '</td>' +
                            '</tr>';
                            $('#courseAssignmentsTableBody').append(row);
                        });
                    } else {
                        $('#noCourseAssignmentsSearchResults').show();
                    }
                } else {
                    $('#searchCourseAssignmentsInfo').hide();
                    alert('Search failed: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                isSearchingCourseAssignments = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);
                $('#searchCourseAssignmentsInfo').hide();
                console.error('Search error:', error);
                alert('An error occurred during search. Please try again.');
            }
        });
    }

    $('#searchCourseAssignmentsInput').on('input', function() {
        const searchTerm = $(this).val().trim();
        clearTimeout(courseAssignmentsSearchTimeout);
        
        if (searchTerm === '') {
            clearTimeout(courseAssignmentsSearchTimeout);
            courseAssignmentsSearchTimeout = setTimeout(function() {
                $('#courseAssignmentsTableBody').html(originalCourseAssignmentsHtml);
                $('#searchCourseAssignmentsInfo').hide();
                $('#noCourseAssignmentsSearchResults').hide();
            }, 300);
        } else {
            courseAssignmentsSearchTimeout = setTimeout(function() {
                performCourseAssignmentsSearch(searchTerm);
            }, 500);
        }
    });

    $('#searchCourseAssignmentsForm').on('submit', function(e) {
        e.preventDefault();
        clearTimeout(courseAssignmentsSearchTimeout);
        const searchTerm = $('#searchCourseAssignmentsInput').val().trim();
        if (searchTerm === '') {
            $('#courseAssignmentsTableBody').html(originalCourseAssignmentsHtml);
            $('#searchCourseAssignmentsInfo').hide();
            $('#noCourseAssignmentsSearchResults').hide();
        } else {
            performCourseAssignmentsSearch(searchTerm);
        }
    });
});
</script>

<?= $this->include('template/footer') ?>

