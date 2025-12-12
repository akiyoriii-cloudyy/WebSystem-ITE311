<?= $this->include('template/header') ?>

<div class="container mt-4">
  <h3 class="mb-3">My Students</h3>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <!-- ✅ Search Form for My Students -->
  <div class="card mb-3">
    <div class="card-body">
      <form id="searchMyStudentsForm" method="GET" action="javascript:void(0);">
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
                id="searchMyStudentsInput" 
                name="q" 
                placeholder="Search students by name, email, course, or student ID..." 
                value=""
                autocomplete="off">
            </div>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100" id="searchMyStudentsBtn">
              Search
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- ✅ Search Results Info -->
  <div id="searchMyStudentsInfo" class="mb-3" style="display: none;">
    <p class="text-muted">
      <span id="myStudentsResultCount">0</span> result(s) found
    </p>
  </div>

  <?php if (!empty($students)): ?>
    <div class="table-responsive">
      <table class="table table-bordered align-middle" id="myStudentsTable">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Student Name</th>
            <th>Email</th>
            <th>Student ID</th>
            <th>Course</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="myStudentsTableBody">
          <?php foreach ($students as $i => $student): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><?= esc($student['student_name'] ?? 'N/A') ?></td>
              <td><?= esc($student['email'] ?? 'N/A') ?></td>
              <td>
                <?php if (!empty($student['student_id'])): ?>
                  <span class="badge bg-secondary"><?= esc($student['student_id']) ?></span>
                <?php else: ?>
                  <span class="text-muted">-</span>
                <?php endif; ?>
              </td>
              <td><?= esc($student['course_title'] ?? 'N/A') ?></td>
              <td>
                <a class="btn btn-sm btn-info" href="<?= base_url('teacher/courses/view/' . $student['course_id']) ?>">View Course</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div id="noMyStudentsSearchResults" style="display: none;">
      <p class="text-muted mb-0 text-center">No students found matching your search.</p>
    </div>
  <?php else: ?>
    <div class="alert alert-info">No students found.</div>
  <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // ✅ MY STUDENTS SEARCH FUNCTIONALITY
    let myStudentsSearchTimeout;
    let isSearchingMyStudents = false;
    const originalMyStudentsHtml = $('#myStudentsTableBody').html(); // Store original students
    
    function performMyStudentsSearch(searchTerm) {
        if (isSearchingMyStudents) return;
        
        isSearchingMyStudents = true;
        const $searchBtn = $('#searchMyStudentsBtn');
        const originalBtnText = $searchBtn.html();
        
        $searchBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Searching...');

        $.ajax({
            url: "<?= base_url('teacher/search/my-students') ?>",
            type: "GET",
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                q: searchTerm
            },
            dataType: 'json',
            success: function(response) {
                isSearchingMyStudents = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);

                if (response.status === 'success') {
                    $('#myStudentsResultCount').text(response.count);
                    $('#searchMyStudentsInfo').show();
                    $('#myStudentsTableBody').empty();
                    $('#noMyStudentsSearchResults').hide();

                    if (response.results.length > 0) {
                        response.results.forEach(function(student, index) {
                            const studentIdHtml = student.student_id 
                                ? '<span class="badge bg-secondary">' + student.student_id + '</span>'
                                : '<span class="text-muted">-</span>';
                            
                            const row = '<tr>' +
                                '<td>' + (index + 1) + '</td>' +
                                '<td>' + (student.student_name || 'N/A') + '</td>' +
                                '<td>' + (student.email || 'N/A') + '</td>' +
                                '<td>' + studentIdHtml + '</td>' +
                                '<td>' + (student.course_title || 'N/A') + '</td>' +
                                '<td>' +
                                '<a class="btn btn-sm btn-info" href="<?= base_url("teacher/courses/view/") ?>' + student.course_id + '">View Course</a>' +
                                '</td>' +
                            '</tr>';
                            $('#myStudentsTableBody').append(row);
                        });
                    } else {
                        $('#noMyStudentsSearchResults').show();
                    }
                } else {
                    $('#searchMyStudentsInfo').hide();
                    alert('Search failed: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                isSearchingMyStudents = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);
                $('#searchMyStudentsInfo').hide();
                console.error('Search error:', error);
                alert('An error occurred during search. Please try again.');
            }
        });
    }

    $('#searchMyStudentsInput').on('input', function() {
        const searchTerm = $(this).val().trim();
        clearTimeout(myStudentsSearchTimeout);
        
        if (searchTerm === '') {
            clearTimeout(myStudentsSearchTimeout);
            myStudentsSearchTimeout = setTimeout(function() {
                $('#myStudentsTableBody').html(originalMyStudentsHtml);
                $('#searchMyStudentsInfo').hide();
                $('#noMyStudentsSearchResults').hide();
            }, 300);
        } else {
            myStudentsSearchTimeout = setTimeout(function() {
                performMyStudentsSearch(searchTerm);
            }, 500);
        }
    });

    $('#searchMyStudentsForm').on('submit', function(e) {
        e.preventDefault();
        clearTimeout(myStudentsSearchTimeout);
        const searchTerm = $('#searchMyStudentsInput').val().trim();
        if (searchTerm === '') {
            $('#myStudentsTableBody').html(originalMyStudentsHtml);
            $('#searchMyStudentsInfo').hide();
            $('#noMyStudentsSearchResults').hide();
        } else {
            performMyStudentsSearch(searchTerm);
        }
    });
});
</script>

<?= $this->include('template/footer') ?>

