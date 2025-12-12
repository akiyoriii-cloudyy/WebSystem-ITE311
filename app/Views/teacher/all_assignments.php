<?= $this->include('template/header') ?>

<div class="container mt-4">
  <h3 class="mb-3">All Assignments</h3>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <!-- ✅ Search Form for Assignments -->
  <div class="card mb-3">
    <div class="card-body">
      <form id="searchAssignmentsForm" method="GET" action="javascript:void(0);">
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
                id="searchAssignmentsInput" 
                name="q" 
                placeholder="Search assignments by title, type, course, or due date..." 
                value=""
                autocomplete="off">
            </div>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100" id="searchAssignmentsBtn">
              Search
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- ✅ Search Results Info -->
  <div id="searchAssignmentsInfo" class="mb-3" style="display: none;">
    <p class="text-muted">
      <span id="assignmentsResultCount">0</span> result(s) found
    </p>
  </div>

  <?php if (!empty($assignments)): ?>
    <div class="table-responsive">
      <table class="table table-bordered align-middle" id="assignmentsTable">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Course</th>
            <th>Type</th>
            <th>Title</th>
            <th>Max Score</th>
            <th>Due Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="assignmentsTableBody">
          <?php foreach ($assignments as $i => $assignment): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><?= esc($assignment['course_title'] ?? 'N/A') ?></td>
              <td><span class="badge bg-info"><?= esc($assignment['assignment_type'] ?? 'N/A') ?></span></td>
              <td><?= esc($assignment['title'] ?? 'N/A') ?></td>
              <td><?= number_format($assignment['max_score'] ?? 0, 2) ?></td>
              <td>
                <?php if (!empty($assignment['due_date'])): ?>
                  <?= date('M d, Y H:i', strtotime($assignment['due_date'])) ?>
                <?php else: ?>
                  <span class="text-muted">N/A</span>
                <?php endif; ?>
              </td>
              <td>
                <a href="<?= site_url('teacher/assignments/' . $assignment['id'] . '/grade') ?>" class="btn btn-sm btn-success">Grade</a>
                <a href="<?= site_url('teacher/courses/' . $assignment['course_id'] . '/assignments') ?>" class="btn btn-sm btn-info">Manage</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div id="noAssignmentsSearchResults" style="display: none;">
      <p class="text-muted mb-0 text-center">No assignments found matching your search.</p>
    </div>
  <?php else: ?>
    <div class="alert alert-info">No assignments found.</div>
  <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // ✅ ASSIGNMENTS SEARCH FUNCTIONALITY
    let assignmentsSearchTimeout;
    let isSearchingAssignments = false;
    const originalAssignmentsHtml = $('#assignmentsTableBody').html(); // Store original assignments
    
    function performAssignmentsSearch(searchTerm) {
        if (isSearchingAssignments) return;
        
        isSearchingAssignments = true;
        const $searchBtn = $('#searchAssignmentsBtn');
        const originalBtnText = $searchBtn.html();
        
        $searchBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Searching...');

        $.ajax({
            url: "<?= base_url('teacher/search/assignments') ?>",
            type: "GET",
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                q: searchTerm
            },
            dataType: 'json',
            success: function(response) {
                isSearchingAssignments = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);

                if (response.status === 'success') {
                    $('#assignmentsResultCount').text(response.count);
                    $('#searchAssignmentsInfo').show();
                    $('#assignmentsTableBody').empty();
                    $('#noAssignmentsSearchResults').hide();

                    if (response.results.length > 0) {
                        response.results.forEach(function(assignment, index) {
                            const dueDateHtml = assignment.due_date 
                                ? new Date(assignment.due_date).toLocaleString('en-US', {year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'})
                                : '<span class="text-muted">N/A</span>';
                            
                            const row = '<tr>' +
                                '<td>' + (index + 1) + '</td>' +
                                '<td>' + (assignment.course_title || 'N/A') + '</td>' +
                                '<td><span class="badge bg-info">' + (assignment.assignment_type || 'N/A') + '</span></td>' +
                                '<td>' + (assignment.title || 'N/A') + '</td>' +
                                '<td>' + parseFloat(assignment.max_score || 0).toFixed(2) + '</td>' +
                                '<td>' + dueDateHtml + '</td>' +
                                '<td>' +
                                '<a href="<?= site_url("teacher/assignments/") ?>' + assignment.id + '/grade" class="btn btn-sm btn-success">Grade</a> ' +
                                '<a href="<?= site_url("teacher/courses/") ?>' + assignment.course_id + '/assignments" class="btn btn-sm btn-info">Manage</a>' +
                                '</td>' +
                            '</tr>';
                            $('#assignmentsTableBody').append(row);
                        });
                    } else {
                        $('#noAssignmentsSearchResults').show();
                    }
                } else {
                    $('#searchAssignmentsInfo').hide();
                    alert('Search failed: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                isSearchingAssignments = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);
                $('#searchAssignmentsInfo').hide();
                console.error('Search error:', error);
                alert('An error occurred during search. Please try again.');
            }
        });
    }

    $('#searchAssignmentsInput').on('input', function() {
        const searchTerm = $(this).val().trim();
        clearTimeout(assignmentsSearchTimeout);
        
        if (searchTerm === '') {
            clearTimeout(assignmentsSearchTimeout);
            assignmentsSearchTimeout = setTimeout(function() {
                $('#assignmentsTableBody').html(originalAssignmentsHtml);
                $('#searchAssignmentsInfo').hide();
                $('#noAssignmentsSearchResults').hide();
            }, 300);
        } else {
            assignmentsSearchTimeout = setTimeout(function() {
                performAssignmentsSearch(searchTerm);
            }, 500);
        }
    });

    $('#searchAssignmentsForm').on('submit', function(e) {
        e.preventDefault();
        clearTimeout(assignmentsSearchTimeout);
        const searchTerm = $('#searchAssignmentsInput').val().trim();
        if (searchTerm === '') {
            $('#assignmentsTableBody').html(originalAssignmentsHtml);
            $('#searchAssignmentsInfo').hide();
            $('#noAssignmentsSearchResults').hide();
        } else {
            performAssignmentsSearch(searchTerm);
        }
    });
});
</script>

<?= $this->include('template/footer') ?>

