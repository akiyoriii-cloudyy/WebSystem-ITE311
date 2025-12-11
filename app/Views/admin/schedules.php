<?= $this->include('template/header') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Course Schedules Management</h2>
        <a href="<?= site_url('admin_dashboard') ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Create Schedule Form -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Create New Schedule</h5>
        </div>
        <div class="card-body">
            <form id="createScheduleForm">
                <?= csrf_field() ?>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="course_id" class="form-label">Course <span class="text-danger">*</span></label>
                        <select class="form-select" id="course_id" name="course_id" required>
                            <option value="">Select Course</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= $course['id'] ?>">
                                    <?= esc($course['title']) ?>
                                    <?php if (!empty($course['course_number'])): ?>
                                        (<?= esc($course['course_number']) ?>)
                                    <?php endif; ?>
                                    - <?= esc($course['instructor_name'] ?? 'N/A') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="class_type" class="form-label">Class Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="class_type" name="class_type" required>
                            <option value="">Select Type</option>
                            <option value="online">Online</option>
                            <option value="face_to_face">Face-to-Face</option>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="day_of_week" class="form-label">Day of Week <span class="text-danger">*</span></label>
                        <select class="form-select" id="day_of_week" name="day_of_week" required>
                            <option value="">Select Day</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="start_time" class="form-label">Start Time <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="start_time" name="start_time" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="end_time" class="form-label">End Time <span class="text-danger">*</span></label>
                        <input type="time" class="form-control" id="end_time" name="end_time" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="room" class="form-label">Room/Location</label>
                        <input type="text" class="form-control" id="room" name="room" placeholder="e.g., Room 101">
                    </div>
                </div>
                <div class="row" id="meeting_link_row" style="display: none;">
                    <div class="col-md-12 mb-3">
                        <label for="meeting_link" class="form-label">Meeting Link (for Online Classes) <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="meeting_link" name="meeting_link" placeholder="https://zoom.us/j/... or https://meet.google.com/...">
                        <small class="form-text text-muted">Required for online classes</small>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Create Schedule
                </button>
                <button type="reset" class="btn btn-secondary" id="resetBtn">Reset</button>
            </form>
        </div>
    </div>

    <!-- Schedules Table -->
    <div class="card">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">All Course Schedules</h5>
        </div>
        <div class="card-body">
            <?php if (!empty($schedules)): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Course</th>
                                <th>Instructor</th>
                                <th>Class Type</th>
                                <th>Day</th>
                                <th>Time</th>
                                <th>Room/Location</th>
                                <th>Meeting Link</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($schedules as $index => $schedule): ?>
                                <tr>
                                    <td><?= $index + 1 ?></td>
                                    <td>
                                        <strong><?= esc($schedule['course_title']) ?></strong>
                                        <?php if (!empty($schedule['course_number'])): ?>
                                            <br><small class="text-muted"><?= esc($schedule['course_number']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($schedule['instructor_name'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php if ($schedule['class_type'] === 'online'): ?>
                                            <span class="badge bg-info">Online</span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">Face-to-Face</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= esc($schedule['day_of_week']) ?></td>
                                    <td>
                                        <?= date('h:i A', strtotime($schedule['start_time'])) ?> - 
                                        <?= date('h:i A', strtotime($schedule['end_time'])) ?>
                                    </td>
                                    <td><?= esc($schedule['room'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php if (!empty($schedule['meeting_link'])): ?>
                                            <a href="<?= esc($schedule['meeting_link']) ?>" target="_blank" class="btn btn-sm btn-link">
                                                <i class="bi bi-link-45deg"></i> Join
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-sm btn-warning" onclick="editSchedule(<?= $schedule['id'] ?>)">
                                                <i class="bi bi-pencil"></i> Edit
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" onclick="deleteSchedule(<?= $schedule['id'] ?>)">
                                                <i class="bi bi-trash"></i> Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-info text-center">
                    <p class="mb-0">No schedules found. Create a schedule to get started.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Edit Schedule Modal -->
<div class="modal fade" id="editScheduleModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Schedule</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editScheduleForm">
                <?= csrf_field() ?>
                <input type="hidden" id="edit_schedule_id" name="schedule_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_course_id" class="form-label">Course <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_course_id" name="course_id" required>
                            <option value="">Select Course</option>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?= $course['id'] ?>">
                                    <?= esc($course['title']) ?>
                                    <?php if (!empty($course['course_number'])): ?>
                                        (<?= esc($course['course_number']) ?>)
                                    <?php endif; ?>
                                    - <?= esc($course['instructor_name'] ?? 'N/A') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_class_type" class="form-label">Class Type <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_class_type" name="class_type" required>
                            <option value="">Select Type</option>
                            <option value="online">Online</option>
                            <option value="face_to_face">Face-to-Face</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="edit_day_of_week" class="form-label">Day of Week <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_day_of_week" name="day_of_week" required>
                                <option value="">Select Day</option>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                                <option value="Sunday">Sunday</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_start_time" class="form-label">Start Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="edit_start_time" name="start_time" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="edit_end_time" class="form-label">End Time <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" id="edit_end_time" name="end_time" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="edit_room" class="form-label">Room/Location</label>
                        <input type="text" class="form-control" id="edit_room" name="room" placeholder="e.g., Room 101">
                    </div>
                    <div class="mb-3" id="edit_meeting_link_row" style="display: none;">
                        <label for="edit_meeting_link" class="form-label">Meeting Link (for Online Classes) <span class="text-danger">*</span></label>
                        <input type="url" class="form-control" id="edit_meeting_link" name="meeting_link" placeholder="https://zoom.us/j/... or https://meet.google.com/...">
                        <small class="form-text text-muted">Required for online classes</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Create Schedule
document.getElementById('createScheduleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('<?= site_url('admin/schedules/create') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            // Refresh notifications before reloading page
            if (typeof fetchNotifications === 'function') {
                fetchNotifications();
            }
            setTimeout(() => {
                location.reload();
            }, 500);
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});

// Update Schedule
document.getElementById('editScheduleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const scheduleId = document.getElementById('edit_schedule_id').value;
    const formData = new FormData(this);
    
    fetch('<?= site_url('admin/schedules/update') ?>/' + scheduleId, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('editScheduleModal')).hide();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
});

// Delete Schedule
function deleteSchedule(scheduleId) {
    if (!confirm('Are you sure you want to delete this schedule?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
    
    fetch('<?= site_url('admin/schedules/delete') ?>/' + scheduleId, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

// Show/hide meeting link based on class type for CREATE form
document.getElementById('class_type').addEventListener('change', function() {
    toggleMeetingLinkField(this.value, 'meeting_link_row', 'meeting_link');
});

// Handle form reset
document.getElementById('resetBtn').addEventListener('click', function() {
    setTimeout(() => {
        const meetingLinkRow = document.getElementById('meeting_link_row');
        meetingLinkRow.style.display = 'none';
        document.getElementById('meeting_link').removeAttribute('required');
    }, 0);
});

// Function to toggle meeting link field visibility
function toggleMeetingLinkField(classType, rowId, inputId) {
    const meetingLinkRow = document.getElementById(rowId);
    const meetingLinkInput = document.getElementById(inputId);
    
    if (classType === 'online') {
        meetingLinkRow.style.display = 'block';
        meetingLinkInput.setAttribute('required', 'required');
    } else {
        meetingLinkRow.style.display = 'none';
        meetingLinkInput.removeAttribute('required');
        meetingLinkInput.value = ''; // Clear the value when hidden
    }
}

// Show/hide meeting link based on class type for EDIT form
document.getElementById('edit_class_type').addEventListener('change', function() {
    toggleMeetingLinkField(this.value, 'edit_meeting_link_row', 'edit_meeting_link');
});

// Handle initial state when editing a schedule
function editSchedule(scheduleId) {
    // Fetch schedule data
    fetch('<?= site_url('admin/schedules') ?>?schedule_id=' + scheduleId, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success' && data.schedule) {
            const s = data.schedule;
            document.getElementById('edit_schedule_id').value = s.id;
            document.getElementById('edit_course_id').value = s.course_id;
            document.getElementById('edit_class_type').value = s.class_type;
            document.getElementById('edit_day_of_week').value = s.day_of_week;
            document.getElementById('edit_start_time').value = s.start_time;
            document.getElementById('edit_end_time').value = s.end_time;
            document.getElementById('edit_room').value = s.room || '';
            document.getElementById('edit_meeting_link').value = s.meeting_link || '';
            
            // Show/hide meeting link based on class type
            toggleMeetingLinkField(s.class_type, 'edit_meeting_link_row', 'edit_meeting_link');
            
            const modal = new bootstrap.Modal(document.getElementById('editScheduleModal'));
            modal.show();
        } else {
            alert('Failed to load schedule data: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to load schedule data.');
    });
}
</script>

<?= $this->include('template/footer') ?>

