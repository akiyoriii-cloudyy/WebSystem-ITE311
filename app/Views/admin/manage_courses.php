<?= $this->include('template/header') ?>

<div class="container mt-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Courses</h3>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCourseModal">
      <i class="bi bi-plus-circle"></i> Create New Course
    </button>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>
  
  <div id="alertBox" class="alert d-none"></div>

  <!-- ✅ Search Form for Courses -->
  <div class="card mb-3">
    <div class="card-body">
      <form id="searchCoursesForm" method="GET" action="javascript:void(0);">
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
                id="searchCoursesInput" 
                name="q" 
                placeholder="Search courses by title, course number, or instructor..." 
                value=""
                autocomplete="off">
            </div>
          </div>
          <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100" id="searchCoursesBtn">
              Search
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>

  <!-- ✅ Search Results Info -->
  <div id="searchCoursesInfo" class="mb-3" style="display: none;">
    <p class="text-muted">
      <span id="coursesResultCount">0</span> result(s) found
    </p>
  </div>

  <?php if (!empty($courses)): ?>
    <div class="table-responsive">
      <table class="table table-bordered align-middle" id="coursesTable">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Course Number</th>
            <th>Course Title</th>
            <th>Instructor</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody id="coursesTableBody">
          <?php foreach ($courses as $i => $c): ?>
            <tr>
              <td><?= $i + 1 ?></td>
              <td>
                <span class="course-number-display" data-course-id="<?= $c['id'] ?>">
                  <?php 
                  $courseNumber = $c['course_number'] ?? '';
                  if (!empty($courseNumber)): ?>
                    <span class="badge bg-secondary"><?= esc($courseNumber) ?></span>
                  <?php else: ?>
                    <span class="text-muted">Not Set</span>
                  <?php endif; ?>
                </span>
                <button type="button" class="btn btn-sm btn-outline-secondary ms-2 edit-course-number-btn" 
                        data-course-id="<?= $c['id'] ?>" 
                        data-course-number="<?= esc($c['course_number'] ?? '') ?>"
                        title="Edit Course Number">
                  <i class="bi bi-pencil"></i> Edit
                </button>
              </td>
              <td><?= esc($c['title'] ?? $c['name'] ?? ('Course #' . $c['id'])) ?></td>
              <td><?= esc($c['instructor_name'] ?? '—') ?></td>
              <td>
                <a class="btn btn-sm btn-primary" href="<?= base_url('admin/course/' . (int)$c['id'] . '/upload') ?>">Upload Materials</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <div id="noCoursesSearchResults" style="display: none;">
      <p class="text-muted mb-0 text-center">No courses found matching your search.</p>
    </div>
  <?php else: ?>
    <div class="alert alert-info">No courses found.</div>
  <?php endif; ?>

  <!-- Create Course Modal -->
  <div class="modal fade" id="createCourseModal" tabindex="-1" aria-labelledby="createCourseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="createCourseModalLabel">Create New Course</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="createCourseForm">
          <?= csrf_field() ?>
          <div class="modal-body">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="course_title" class="form-label">Course Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="course_title" name="title" required 
                       placeholder="e.g., ITE 321 - Web Application Development" maxlength="255">
              </div>
              <div class="col-md-3 mb-3">
                <label for="course_number" class="form-label">Course Number/Code</label>
                <input type="text" class="form-control" id="course_number" name="course_number" 
                       placeholder="e.g., ITE321, CS101" maxlength="50">
                <small class="form-text text-muted">Optional</small>
              </div>
              <div class="col-md-3 mb-3">
                <label for="units" class="form-label">Units</label>
                <input type="number" class="form-control" id="units" name="units" 
                       placeholder="e.g., 3" min="0" max="10" step="1">
                <small class="form-text text-muted">Optional: Number of units (0-10)</small>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="course_description" class="form-label">Description</label>
              <textarea class="form-control" id="course_description" name="description" rows="3" 
                        placeholder="Enter course description..."></textarea>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="instructor_id" class="form-label">Instructor</label>
                <select class="form-select" id="instructor_id" name="instructor_id">
                  <option value="">-- Select Instructor (Optional) --</option>
                  <?php if (!empty($teachers)): ?>
                    <?php foreach ($teachers as $teacher): ?>
                      <option value="<?= esc($teacher['id']) ?>"><?= esc($teacher['name']) ?> (<?= esc($teacher['email']) ?>)</option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
                <small class="form-text text-muted">You can assign an instructor later</small>
              </div>
              <div class="col-md-6 mb-3">
                <label for="acad_year_id" class="form-label">Academic Year</label>
                <select class="form-select" id="acad_year_id" name="acad_year_id">
                  <option value="">-- Select Academic Year (Optional) --</option>
                  <?php if (!empty($acadYears)): ?>
                    <?php foreach ($acadYears as $year): ?>
                      <option value="<?= esc($year['id']) ?>"><?= esc($year['acad_year']) ?></option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="semester_id" class="form-label">Semester</label>
                <select class="form-select" id="semester_id" name="semester_id">
                  <option value="">-- Select Semester (Optional) --</option>
                  <?php if (!empty($semesters)): ?>
                    <?php foreach ($semesters as $semester): ?>
                      <option value="<?= esc($semester['id']) ?>">
                        <?= esc($semester['semester']) ?>
                        <?php if (!empty($semester['acad_year'])): ?>
                          (<?= esc($semester['acad_year']) ?>)
                        <?php endif; ?>
                        <?php if (!empty($semester['is_active'])): ?>
                          - Active
                        <?php endif; ?>
                      </option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
                <small class="form-text text-muted">Select the semester for this course</small>
              </div>
              <div class="col-md-6 mb-3">
                <label for="term_id" class="form-label">Term</label>
                <select class="form-select" id="term_id" name="term_id">
                  <option value="">-- Select Term (Optional) --</option>
                  <?php if (!empty($terms)): ?>
                    <?php foreach ($terms as $term): ?>
                      <option value="<?= esc($term['id']) ?>" data-semester-id="<?= esc($term['semester_id'] ?? '') ?>">
                        <?= esc($term['term']) ?>
                        <?php if (!empty($term['semester'])): ?>
                          - <?= esc($term['semester']) ?>
                        <?php endif; ?>
                        <?php if (!empty($term['acad_year'])): ?>
                          (<?= esc($term['acad_year']) ?>)
                        <?php endif; ?>
                        <?php if (!empty($term['is_active'])): ?>
                          - Active
                        <?php endif; ?>
                      </option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
                <small class="form-text text-muted">Select the term for this course</small>
              </div>
            </div>

            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="department_id" class="form-label">Department</label>
                <select class="form-select" id="department_id" name="department_id">
                  <option value="">-- Select Department (Optional) --</option>
                  <?php if (!empty($departments)): ?>
                    <?php foreach ($departments as $dept): ?>
                      <option value="<?= esc($dept['id']) ?>">
                        <?= esc($dept['department_code']) ?> - <?= esc($dept['department_name']) ?>
                      </option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
                <small class="form-text text-muted">Select the department for this course</small>
              </div>
              <div class="col-md-6 mb-3">
                <label for="program_id" class="form-label">Program</label>
                <select class="form-select" id="program_id" name="program_id">
                  <option value="">-- Select Program (Optional) --</option>
                  <?php if (!empty($programs)): ?>
                    <?php foreach ($programs as $prog): ?>
                      <option value="<?= esc($prog['id']) ?>" data-department-id="<?= esc($prog['department_id'] ?? '') ?>">
                        <?= esc($prog['program_code']) ?> - <?= esc($prog['program_name']) ?>
                        <?php if (!empty($prog['department_name'])): ?>
                          (<?= esc($prog['department_code']) ?>)
                        <?php endif; ?>
                      </option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
                <small class="form-text text-muted">Select the program for this course</small>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">
              <span class="spinner-border spinner-border-sm d-none" role="status"></span>
              Create Course
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Course Number Modal -->
  <div class="modal fade" id="editCourseNumberModal" tabindex="-1" aria-labelledby="editCourseNumberModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editCourseNumberModalLabel">Edit Course Number</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form id="editCourseNumberForm">
          <?= csrf_field() ?>
          <div class="modal-body">
            <input type="hidden" id="edit_course_id" name="course_id">
            <div class="mb-3">
              <label for="edit_course_number" class="form-label">Course Number/Code</label>
              <input type="text" class="form-control" id="edit_course_number" name="course_number" 
                     placeholder="e.g., ITE321, CS101" maxlength="50">
              <small class="form-text text-muted">Enter the course number or code (e.g., ITE321, CS101)</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary">Save Changes</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?= $this->include('template/footer') ?>

<script>
// All jQuery code after footer to ensure jQuery is loaded
$(document).ready(function() {
    // Create Course Form Submit
    $('#createCourseForm').submit(function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const spinner = submitBtn.find('.spinner-border');
        
        submitBtn.prop('disabled', true);
        spinner.removeClass('d-none');
        
        $.ajax({
            url: '<?= site_url('admin/courses/create') ?>',
            type: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
                
                if (response.status === 'success') {
                    $('#createCourseModal').modal('hide');
                    form[0].reset();
                    
                    // Update CSRF token
                    if (response.csrf_token && response.csrf_hash) {
                        $('input[name="' + response.csrf_token + '"]').val(response.csrf_hash);
                    }
                    
                    // Show success message
                    $('#alertBox').removeClass('d-none alert-danger')
                                  .addClass('alert-success')
                                  .html('<strong>✓ Success!</strong> ' + response.message);
                    
                    // Reload page after 1.5 seconds to show new course
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    $('#alertBox').removeClass('d-none alert-success')
                                  .addClass('alert-danger')
                                  .html('<strong>✗ Error!</strong> ' + (response.message || 'Failed to create course.'));
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false);
                spinner.addClass('d-none');
                
                let errorMsg = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMsg = response.message;
                        }
                    } catch (e) {
                        errorMsg = xhr.statusText || 'An error occurred. Please try again.';
                    }
                }
                
                $('#alertBox').removeClass('d-none alert-success')
                              .addClass('alert-danger')
                              .html('<strong>✗ Error!</strong> ' + errorMsg);
            }
        });
    });

    // Reset form when modal is closed
    $('#createCourseModal').on('hidden.bs.modal', function() {
        $('#createCourseForm')[0].reset();
        $('#alertBox').addClass('d-none');
        // Reset term dropdown when modal closes
        $('#term_id').val('').trigger('change');
        // Reset program dropdown when modal closes
        $('#program_id').val('').trigger('change');
    });

    // Filter terms based on selected semester
    $('#semester_id').on('change', function() {
        const selectedSemesterId = $(this).val();
        const $termSelect = $('#term_id');
        const $termOptions = $termSelect.find('option[data-semester-id]');
        
        if (!selectedSemesterId) {
            // Show all terms if no semester selected
            $termOptions.show();
            $termSelect.find('option[value=""]').show();
        } else {
            // Hide all terms first
            $termOptions.hide();
            // Show only terms that belong to selected semester
            $termOptions.filter('[data-semester-id="' + selectedSemesterId + '"]').show();
            // Show the default option
            $termSelect.find('option[value=""]').show();
            // Reset term selection if current term doesn't belong to selected semester
            const currentTermSemesterId = $termSelect.find('option:selected').data('semester-id');
            if (currentTermSemesterId && currentTermSemesterId !== selectedSemesterId) {
                $termSelect.val('');
            }
        }
    });

    // Filter programs based on selected department
    $('#department_id').on('change', function() {
        const selectedDeptId = $(this).val();
        const $programSelect = $('#program_id');
        const $programOptions = $programSelect.find('option[data-department-id]');
        
        if (!selectedDeptId) {
            // Show all programs if no department selected
            $programOptions.show();
            $programSelect.find('option[value=""]').show();
        } else {
            // Hide all programs first
            $programOptions.hide();
            // Show only programs that belong to selected department
            $programOptions.filter('[data-department-id="' + selectedDeptId + '"]').show();
            // Show the default option
            $programSelect.find('option[value=""]').show();
            // Reset program selection if current program doesn't belong to selected department
            const currentProgramDeptId = $programSelect.find('option:selected').data('department-id');
            if (currentProgramDeptId && currentProgramDeptId !== selectedDeptId) {
                $programSelect.val('');
            }
        }
    });

    // Edit Course Number Button Click
    $(document).on('click', '.edit-course-number-btn', function() {
        const courseId = $(this).data('course-id');
        const courseNumber = $(this).data('course-number') || '';
        
        $('#edit_course_id').val(courseId);
        $('#edit_course_number').val(courseNumber);
        $('#editCourseNumberModal').modal('show');
    });

    // Edit Course Number Form Submit
    $('#editCourseNumberForm').submit(function(e) {
        e.preventDefault();
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');
        
        $.ajax({
            url: '<?= site_url('admin/courses/update-course-number') ?>',
            type: 'POST',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                submitBtn.prop('disabled', false).html(originalText);
                
                if (response.status === 'success') {
                    $('#editCourseNumberModal').modal('hide');
                    
                    // Update display
                    const courseId = $('#edit_course_id').val();
                    const courseNumber = $('#edit_course_number').val();
                    const displaySpan = $(`.course-number-display[data-course-id="${courseId}"]`);
                    
                    if (courseNumber) {
                        displaySpan.html(`<span class="badge bg-secondary">${courseNumber}</span>`);
                    } else {
                        displaySpan.html('<span class="text-muted">Not Set</span>');
                    }
                    
                    // Update button data
                    $(`.edit-course-number-btn[data-course-id="${courseId}"]`).data('course-number', courseNumber);
                    
                    // Show success message
                    const alert = $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                        '<strong>✓ Success!</strong> ' + response.message +
                        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
                        '</div>');
                    $('.container').prepend(alert);
                    
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                submitBtn.prop('disabled', false).html(originalText);
                let errorMsg = 'An error occurred. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                alert('Error: ' + errorMsg);
            }
        });
    });

    // ✅ COURSES SEARCH FUNCTIONALITY - Updated to match working enrollment search pattern
    let coursesSearchTimeout;
    let isSearchingCourses = false;
    const originalCoursesHtml = $('#coursesTableBody').html(); // Store original courses
    
    function performCoursesSearch(searchTerm) {
        if (isSearchingCourses) return; // Prevent multiple simultaneous searches
        
        isSearchingCourses = true;
        const $searchBtn = $('#searchCoursesBtn');
        const originalBtnText = $searchBtn.html();
        
        // Show loading indicator
        $searchBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Searching...');

        $.ajax({
            url: "<?= base_url('admin/search/courses') ?>",
            type: "GET",
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                q: searchTerm
            },
            dataType: 'json',
            success: function(response) {
                isSearchingCourses = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);

                console.log('Course search response:', response); // Debug log

                if (response.status === 'success') {
                    // Update search info
                    $('#coursesResultCount').text(response.count);
                    $('#searchCoursesInfo').show();

                    // Clear existing courses
                    $('#coursesTableBody').empty();
                    $('#noCoursesSearchResults').hide();

                    if (response.results && response.results.length > 0) {
                        // Build table rows from search results
                        response.results.forEach(function(course, index) {
                            const courseNumber = course.course_number || '';
                            const courseNumberHtml = courseNumber 
                                ? '<span class="badge bg-secondary">' + courseNumber + '</span>'
                                : '<span class="text-muted">Not Set</span>';
                            
                            const row = '<tr>' +
                                '<td>' + (index + 1) + '</td>' +
                                '<td>' +
                                '<span class="course-number-display" data-course-id="' + course.id + '">' + courseNumberHtml + '</span>' +
                                '<button type="button" class="btn btn-sm btn-outline-secondary ms-2 edit-course-number-btn" ' +
                                'data-course-id="' + course.id + '" ' +
                                'data-course-number="' + (courseNumber.replace(/"/g, '&quot;')) + '" ' +
                                'title="Edit Course Number">' +
                                '<i class="bi bi-pencil"></i> Edit' +
                                '</button>' +
                                '</td>' +
                                '<td>' + (course.title || course.name || 'Course #' + course.id) + '</td>' +
                                '<td>' + (course.instructor_name || '—') + '</td>' +
                                '<td>' +
                                '<a class="btn btn-sm btn-primary" href="<?= base_url("admin/course/") ?>' + course.id + '/upload">Upload Materials</a>' +
                                '</td>' +
                            '</tr>';
                            $('#coursesTableBody').append(row);
                        });
                    } else {
                        $('#noCoursesSearchResults').show();
                    }
                } else {
                    $('#searchCoursesInfo').hide();
                    alert('Search failed: ' + (response.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                isSearchingCourses = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);
                $('#searchCoursesInfo').hide();
                console.error('Search error:', error);
                alert('An error occurred during search. Please try again.');
            }
        });
    }

    // Automatic search as user types (with debouncing)
    $('#searchCoursesInput').on('input', function() {
        const searchTerm = $(this).val().trim();
        clearTimeout(coursesSearchTimeout);
        
        if (searchTerm === '') {
            clearTimeout(coursesSearchTimeout);
            coursesSearchTimeout = setTimeout(function() {
                $('#coursesTableBody').html(originalCoursesHtml);
                $('#searchCoursesInfo').hide();
                $('#noCoursesSearchResults').hide();
            }, 300);
        } else {
            coursesSearchTimeout = setTimeout(function() {
                performCoursesSearch(searchTerm);
            }, 500);
        }
    });

    // Submit form for courses search
    $('#searchCoursesForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear any pending timeout
        clearTimeout(coursesSearchTimeout);
        
        // Perform search immediately when form is submitted
        const searchTerm = $('#searchCoursesInput').val().trim();
        if (searchTerm === '') {
            $('#coursesTableBody').html(originalCoursesHtml);
            $('#searchCoursesInfo').hide();
            $('#noCoursesSearchResults').hide();
        } else {
            performCoursesSearch(searchTerm);
        }
    });

    // Also handle search button click directly
    $('#searchCoursesBtn').on('click', function(e) {
        e.preventDefault();
        $('#searchCoursesForm').submit();
    });
});
</script>
