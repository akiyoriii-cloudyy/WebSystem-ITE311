<?= $this->include('template/header') ?>

<div class="container mt-4">
    <h2 class="text-center fw-bold mb-4">Courses</h2>

    <!-- ✅ Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success text-center"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger text-center"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- ✅ Search Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="searchForm" method="GET" action="<?= base_url('courses/search') ?>">
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
                                id="searchInput" 
                                name="q" 
                                placeholder="Search courses by title, description, or code..." 
                                value="<?= esc($search_term ?? '') ?>"
                                autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100" id="searchBtn">
                            Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ✅ Search Results Info -->
    <div id="searchInfo" class="mb-3" style="display: none;">
        <p class="text-muted">
            <span id="resultCount">0</span> result(s) found
            <?php if (!empty($search_term)): ?>
                for "<strong><?= esc($search_term) ?></strong>"
            <?php endif; ?>
        </p>
    </div>

    <!-- ✅ Courses List -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Available Courses</h5>
        </div>
        <div class="card-body">
            <div id="coursesList">
                <?php if (!empty($courses)): ?>
                    <div class="list-group" id="coursesContainer">
                        <?php foreach ($courses as $course): ?>
                            <?php
                                $isEnrolled = false;
                                if ($user_role === 'student' && !empty($enrolledCourses)) {
                                    foreach ($enrolledCourses as $en) {
                                        if ($en['id'] == $course['id']) {
                                            $isEnrolled = true;
                                            break;
                                        }
                                    }
                                }
                            ?>
                            <div class="list-group-item course-item" 
                                 data-course-title="<?= esc(strtolower($course['title'] ?? $course['name'] ?? '')) ?>"
                                 data-course-description="<?= esc(strtolower($course['description'] ?? '')) ?>"
                                 data-course-code="<?= esc(strtolower($course['code'] ?? '')) ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 course-title-text">
                                            <?= esc($course['title'] ?? $course['name'] ?? 'Untitled Course') ?>
                                            <?php if (!empty($course['code'])): ?>
                                                <span class="badge bg-secondary ms-2"><?= esc($course['code']) ?></span>
                                            <?php endif; ?>
                                        </h6>
                                        <?php if (!empty($course['description'])): ?>
                                            <p class="mb-1 text-muted course-description-text">
                                                <?= esc(substr($course['description'], 0, 150)) ?>
                                                <?= strlen($course['description']) > 150 ? '...' : '' ?>
                                            </p>
                                        <?php endif; ?>
                                        <?php if (!empty($course['instructor_name'])): ?>
                                            <small class="text-muted">
                                                <strong>Instructor:</strong> <?= esc($course['instructor_name']) ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ms-3">
                                        <?php if ($user_role === 'student'): ?>
                                            <?php if ($isEnrolled): ?>
                                                <button class="btn btn-sm btn-secondary" disabled>Enrolled</button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-success enroll-btn" data-course-id="<?= esc($course['id']) ?>">
                                                    Enroll
                                                </button>
                                            <?php endif; ?>
                                        <?php elseif ($user_role === 'teacher'): ?>
                                            <a href="<?= base_url('teacher/course/' . (int)$course['id'] . '/upload') ?>" class="btn btn-sm btn-primary">
                                                View Materials
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info" id="noCoursesMessage">
                        <p class="mb-0">No courses available at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- ✅ Empty Search Results Message -->
            <div id="noSearchResults" class="alert alert-warning" style="display: none;">
                <p class="mb-0">No courses found matching your search criteria.</p>
            </div>
        </div>
    </div>

    <!-- ✅ Alert Box for Enrollment Feedback -->
    <div id="alertBox" class="alert mt-3 d-none"></div>
</div>

<!-- ✅ jQuery and Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize enrolled courses set for quick lookup
    const enrolledCourseIds = new Set();
    <?php if ($user_role === 'student' && !empty($enrolledCourses)): ?>
        <?php foreach ($enrolledCourses as $en): ?>
            enrolledCourseIds.add(<?= (int)$en['id'] ?>);
        <?php endforeach; ?>
    <?php endif; ?>

    // ✅ AUTOMATIC SERVER-SIDE SEARCH (Debounced - searches as you type)
    let searchTimeout;
    let isSearching = false;
    
    function performServerSearch(searchTerm) {
        if (isSearching) return; // Prevent multiple simultaneous searches
        
        isSearching = true;
        const $searchBtn = $('#searchBtn');
        const originalBtnText = $searchBtn.html();
        
        // Show loading indicator
        $searchBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Searching...');

        $.ajax({
            url: "<?= base_url('courses/search') ?>",
            type: "GET",
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            data: {
                q: searchTerm
            },
            dataType: 'json',
            success: function(response) {
                isSearching = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);

                if (response.status === 'success') {
                    // Update search info
                    $('#resultCount').text(response.count);
                    $('#searchInfo').show();

                    // Clear existing courses
                    $('#coursesContainer').empty();
                    $('#noCoursesMessage').hide();
                    $('#noSearchResults').hide();

                    if (response.results.length > 0) {
                        // Display search results
                        let html = '';
                        response.results.forEach(function(course) {
                            const isEnrolled = course.is_enrolled;
                            let actionButton = '';
                            
                            <?php if ($user_role === 'student'): ?>
                                if (isEnrolled) {
                                    actionButton = '<button class="btn btn-sm btn-secondary" disabled>Enrolled</button>';
                                } else {
                                    actionButton = '<button class="btn btn-sm btn-success enroll-btn" data-course-id="' + course.id + '">Enroll</button>';
                                }
                            <?php elseif ($user_role === 'teacher'): ?>
                                actionButton = '<a href="<?= base_url('teacher/course/') ?>' + course.id + '/upload" class="btn btn-sm btn-primary">View Materials</a>';
                            <?php endif; ?>

                            html += '<div class="list-group-item course-item" ' +
                                'data-course-title="' + (course.title || '').toLowerCase() + '" ' +
                                'data-course-description="' + (course.description || '').toLowerCase() + '" ' +
                                'data-course-code="' + (course.code || '').toLowerCase() + '">' +
                                '<div class="d-flex justify-content-between align-items-center">' +
                                '<div class="flex-grow-1">' +
                                '<h6 class="mb-1 course-title-text">' + 
                                (course.title || 'Untitled Course') +
                                (course.code ? ' <span class="badge bg-secondary ms-2">' + course.code + '</span>' : '') +
                                '</h6>' +
                                (course.description ? '<p class="mb-1 text-muted course-description-text">' + 
                                (course.description.length > 150 ? course.description.substring(0, 150) + '...' : course.description) + 
                                '</p>' : '') +
                                '<small class="text-muted"><strong>Instructor:</strong> ' + (course.instructor_name || 'N/A') + '</small>' +
                                '</div>' +
                                '<div class="ms-3">' + actionButton + '</div>' +
                                '</div>' +
                                '</div>';
                        });

                        $('#coursesContainer').html(html);
                        $('#coursesContainer').data('searched', true); // Mark that we've done a server search
                        
                        // Re-initialize enroll buttons for new results
                        initializeEnrollButtons();
                    } else {
                        $('#noSearchResults').show();
                    }
                } else {
                    $('#alertBox').removeClass('d-none alert-success')
                                  .addClass('alert alert-danger')
                                  .text(response.message || 'Search failed. Please try again.');
                }
            },
            error: function(xhr, status, error) {
                isSearching = false;
                $searchBtn.prop('disabled', false).html(originalBtnText);
                
                // Try to parse error response
                let errorMessage = 'An error occurred during search. Please try again.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseText) {
                    try {
                        const errorData = JSON.parse(xhr.responseText);
                        if (errorData.message) {
                            errorMessage = errorData.message;
                        }
                    } catch (e) {
                        // If not JSON, use default message
                    }
                }
                
                $('#alertBox').removeClass('d-none alert-success')
                              .addClass('alert alert-danger')
                              .text(errorMessage);
                console.error('Search error:', {
                    status: xhr.status,
                    statusText: xhr.statusText,
                    error: error,
                    responseText: xhr.responseText
                });
            }
        });
    }
    
    // Automatic search as user types (with debouncing)
    $('#searchInput').on('input', function() {
        const searchTerm = $(this).val().trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        if (searchTerm === '') {
            // If search is empty, reload all courses
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                // Reload page to show all courses
                window.location.href = "<?= base_url('courses') ?>";
            }, 300);
        } else {
            // Debounce: wait 500ms after user stops typing before searching
            searchTimeout = setTimeout(function() {
                performServerSearch(searchTerm);
            }, 500);
        }
    });

<｜tool▁sep｜>new_string
    // ✅ SERVER-SIDE SEARCH (AJAX - Submit form - uses same function)
    $('#searchForm').on('submit', function(e) {
        e.preventDefault();
        
        // Clear any pending timeout
        clearTimeout(searchTimeout);
        
        // Perform search immediately when form is submitted
        const searchTerm = $('#searchInput').val().trim();
        performServerSearch(searchTerm);
    });

    // ✅ Initialize Enroll Buttons Function
    function initializeEnrollButtons() {
        $('.enroll-btn').off('click').on('click', function() {
            const courseId = $(this).data('course-id');
            const button = $(this);
            const courseTitle = button.closest('.course-item').find('.course-title-text').text().trim();

            $.ajax({
                url: "<?= base_url('course/enroll') ?>",
                type: "POST",
                data: {
                    course_id: courseId,
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // Update button state
                        button.prop('disabled', true)
                              .removeClass('btn-success')
                              .addClass('btn-secondary')
                              .text('Enrolled');
                        
                        enrolledCourseIds.add(courseId);

                        // Show success alert
                        $('#alertBox').removeClass('d-none alert-danger')
                                      .addClass('alert alert-success')
                                      .text(response.message);
                        
                        // Auto-hide alert after 3 seconds
                        setTimeout(function() {
                            $('#alertBox').addClass('d-none');
                        }, 3000);
                    } else {
                        $('#alertBox').removeClass('d-none alert-success')
                                      .addClass('alert alert-danger')
                                      .text(response.message);
                    }
                },
                error: function() {
                    $('#alertBox').removeClass('d-none alert-success')
                                  .addClass('alert alert-danger')
                                  .text('An error occurred. Please try again.');
                }
            });
        });
    }

    // Initialize enroll buttons on page load
    initializeEnrollButtons();
});
</script>

<?= $this->include('template/footer') ?>

