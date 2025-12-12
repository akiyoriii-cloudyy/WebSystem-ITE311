<?= $this->include('template/header') ?>

<div class="container mt-4">
  <h3 class="mb-3">My Courses</h3>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <!-- ✅ Search Form for My Courses -->
  <div class="card mb-3">
    <div class="card-body">
      <form id="searchMyCoursesForm" method="GET" action="javascript:void(0);">
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
                id="searchMyCoursesInput" 
                name="q" 
                placeholder="Search courses by title or course number..." 
                value=""
                autocomplete="off">
            </div>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100" id="searchMyCoursesBtn">
              Search
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- ✅ Search Results Info -->
  <div id="searchMyCoursesInfo" class="mb-3" style="display: none;">
    <p class="text-muted">
      <span id="myCoursesResultCount">0</span> result(s) found
    </p>
  </div>

  <?php if (!empty($courses)): ?>
    <div class="table-responsive">
      <table class="table table-bordered align-middle" id="myCoursesTable">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Course</th>
            <th>Students</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="myCoursesTableBody">
          <?php foreach ($courses as $i => $c): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td><?= esc($c['title'] ?? $c['name'] ?? ('Course #' . $c['id'])) ?></td>
              <td><span class="badge bg-secondary"><?= (int)($c['student_count'] ?? 0) ?></span></td>
              <td>
                <a class="btn btn-sm btn-success" href="<?= base_url('teacher/course/' . (int)$c['id'] . '/upload') ?>">Upload Materials</a>
                <a class="btn btn-sm btn-outline-primary" href="<?= base_url('teacher/courses/view/' . (int)$c['id']) ?>">View</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div id="noMyCoursesSearchResults" style="display: none;">
      <p class="text-muted mb-0 text-center">No courses found matching your search.</p>
    </div>
  <?php else: ?>
    <div class="alert alert-info">No courses found.</div>
  <?php endif; ?>
</div>

<?= $this->include('template/footer') ?>

<script>
// ✅ MY COURSES SEARCH FUNCTIONALITY - Moved after footer to ensure jQuery is loaded
// jQuery is loaded via template/footer
$(document).ready(function() {
    let myCoursesSearchTimeout;
    let isSearchingMyCourses = false;
    const originalMyCoursesHtml = $('#myCoursesTableBody').html(); // Store original courses
    
    function performMyCoursesSearch(searchTerm) {
        if (isSearchingMyCourses) return; // Prevent multiple simultaneous searches
        
        isSearchingMyCourses = true;
        const $searchBtn = $('#searchMyCoursesBtn');
        const originalBtnText = $searchBtn.html();
        
        // Show loading indicator
        $searchBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Searching...');

        $.ajax({
            url: "<?= base_url('teacher/search/my-courses') ?>",
            type: "GET",
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                q: searchTerm
            },
            dataType: 'json',
            success: function(response) {
                isSearchingMyCourses = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);

                console.log('My courses search response:', response); // Debug log

                if (response.status === 'success') {
                    // Update search info
                    $('#myCoursesResultCount').text(response.count);
                    $('#searchMyCoursesInfo').show();

                    // Clear existing courses
                    $('#myCoursesTableBody').empty();
                    $('#noMyCoursesSearchResults').hide();

                    if (response.results && response.results.length > 0) {
                        // Build table rows from search results
                        response.results.forEach(function(course, index) {
                            const row = '<tr>' +
                                '<td>' + (index + 1) + '</td>' +
                                '<td>' + (course.title || course.name || 'Course #' + course.id) + '</td>' +
                                '<td><span class="badge bg-secondary">' + (course.student_count || 0) + '</span></td>' +
                                '<td>' +
                                '<a class="btn btn-sm btn-success" href="<?= base_url("teacher/course/") ?>' + course.id + '/upload">Upload Materials</a> ' +
                                '<a class="btn btn-sm btn-outline-primary" href="<?= base_url("teacher/courses/view/") ?>' + course.id + '">View</a>' +
                                '</td>' +
                            '</tr>';
                            $('#myCoursesTableBody').append(row);
                        });
                    } else {
                        $('#noMyCoursesSearchResults').show();
                    }
                } else {
                    $('#searchMyCoursesInfo').hide();
                    alert('Search failed: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                isSearchingMyCourses = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);
                $('#searchMyCoursesInfo').hide();
                
                console.error('Search error:', error);
                console.error('XHR status:', xhr.status);
                console.error('Response text:', xhr.responseText);
                
                let errorMsg = 'An error occurred during search. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const errorData = JSON.parse(xhr.responseText);
                        if (errorData.message) {
                            errorMsg = errorData.message;
                        }
                    } catch (e) {
                        console.error('Could not parse error response');
                    }
                }
                
                alert('Search Error: ' + errorMsg);
            }
        });
    }

    // Automatic search as user types (with debouncing)
    $('#searchMyCoursesInput').on('input', function() {
        const searchTerm = $(this).val().trim();
        clearTimeout(myCoursesSearchTimeout);
        
        if (searchTerm === '') {
            clearTimeout(myCoursesSearchTimeout);
            myCoursesSearchTimeout = setTimeout(function() {
                $('#myCoursesTableBody').html(originalMyCoursesHtml);
                $('#searchMyCoursesInfo').hide();
                $('#noMyCoursesSearchResults').hide();
            }, 300);
        } else {
            myCoursesSearchTimeout = setTimeout(function() {
                performMyCoursesSearch(searchTerm);
            }, 500);
        }
    });

    // Submit form for my courses search
    $('#searchMyCoursesForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear any pending timeout
        clearTimeout(myCoursesSearchTimeout);
        
        // Perform search immediately when form is submitted
        const searchTerm = $('#searchMyCoursesInput').val().trim();
        if (searchTerm === '') {
            $('#myCoursesTableBody').html(originalMyCoursesHtml);
            $('#searchMyCoursesInfo').hide();
            $('#noMyCoursesSearchResults').hide();
        } else {
            performMyCoursesSearch(searchTerm);
        }
    });

    // Also handle search button click directly
    $('#searchMyCoursesBtn').on('click', function(e) {
        e.preventDefault();
        $('#searchMyCoursesForm').submit();
    });
});
</script>
