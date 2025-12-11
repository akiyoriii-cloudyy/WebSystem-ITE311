</div> <!-- end content -->

<!-- jQuery (required for AJAX) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Notification System Script -->
<script>
$(document).ready(function() {
    
    // Function to fetch notifications from server
    function fetchNotifications() {
        console.log('Fetching notifications...');
        $.ajax({
            url: '<?= base_url('notifications') ?>',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log('Notification fetch response:', response);
                if (response.status === 'success') {
                    console.log('Unread count:', response.unread_count);
                    console.log('Notifications:', response.notifications);
                    updateNotificationUI(response.unread_count, response.notifications);
                } else {
                    console.error('Notification fetch failed:', response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching notifications:', error);
                console.error('Status:', status);
                console.error('XHR Response:', xhr.responseText);
                console.error('XHR Status:', xhr.status);
            }
        });
    }

    // Function to update the notification UI
    function updateNotificationUI(unreadCount, notifications) {
        const badge = $('#notification-badge');
        const notificationList = $('#notification-list');
        const markAllBtn = $('#markAllAsRead');

        // Update badge count
        if (unreadCount > 0) {
            badge.text(unreadCount).show();
            markAllBtn.show(); // Show "Mark All as Read" button
        } else {
            badge.hide();
            markAllBtn.hide(); // Hide button when no unread notifications
        }

        // Clear existing notifications
        notificationList.empty();

        // If no notifications, show empty message
        if (notifications.length === 0) {
            notificationList.html('<div class="notification-empty">No new notifications</div>');
            return;
        }

        // Populate notification list
        notifications.forEach(function(notification) {
            const isRead = notification.is_read == 1;
            const readClass = isRead ? 'text-muted' : '';
            
            // Use read_at timestamp for read notifications, created_at for unread
            const displayTime = isRead && notification.read_at ? notification.read_at : notification.created_at;
            const timeLabel = isRead && notification.read_at ? 'Marked as read ' : '';
            
            const notificationItem = `
                <div class="notification-item ${readClass}" data-id="${notification.id}">
                    <div class="notification-message">${notification.message}</div>
                    <div class="notification-time">${timeLabel}${formatDate(displayTime)}</div>
                    ${!isRead ? `<div style="margin-top: 8px;"><a href="#" class="notification-mark-read" data-id="${notification.id}">✓ Mark as read</a></div>` : '<div style="margin-top: 8px; font-size: 0.8rem; color: #28a745;">✓ Read</div>'}
                </div>
            `;
            
            notificationList.append(notificationItem);
        });
    }

    // Function to format date/time
    function formatDate(dateString) {
        if (!dateString) return '';
        
        const date = new Date(dateString);
        const now = new Date();
        const diff = Math.floor((now - date) / 1000); // difference in seconds

        if (diff < 60) return 'Just now';
        if (diff < 3600) return Math.floor(diff / 60) + ' minutes ago';
        if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
        if (diff < 604800) return Math.floor(diff / 86400) + ' days ago';
        
        return date.toLocaleDateString();
    }

    // Toggle notification dropdown
    $('#notification-bell').on('click', function(e) {
        e.stopPropagation();
        $('#notification-dropdown').toggleClass('show');
    });

    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.notification-bell').length) {
            $('#notification-dropdown').removeClass('show');
        }
    });

    // Mark notification as read
    $(document).on('click', '.notification-mark-read', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const notificationId = $(this).data('id');
        console.log('Marking notification as read:', notificationId);
        
        // Show loading state
        $(this).text('⏳ Marking...');
        
        $.ajax({
            url: '<?= base_url('notifications/mark_read') ?>/' + notificationId,
            type: 'POST',
            data: {
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                console.log('Mark as read response:', response);
                if (response.status === 'success') {
                    // Refresh notifications
                    fetchNotifications();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error marking notification as read:', error);
                console.error('XHR Response:', xhr.responseText);
                alert('Failed to mark notification as read. Please check console for details.');
            }
        });
        
        return false;
    });

    // Mark all notifications as read
    $('#markAllAsRead').on('click', function(e) {
        e.preventDefault();
        
        // Show loading state
        $(this).text('⏳ Marking all...');
        $(this).prop('disabled', true);
        
        $.ajax({
            url: '<?= base_url('notifications/mark_all_read') ?>',
            type: 'POST',
            data: {
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    // Refresh notifications
                    fetchNotifications();
                    
                    // Show success feedback
                    console.log('All notifications marked as read');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error marking all notifications as read:', error);
                alert('Failed to mark all as read. Please try again.');
                
                // Reset button
                $('#markAllAsRead').text('✓ Mark All as Read').prop('disabled', false);
            }
        });
    });

    // Initial fetch on page load
    fetchNotifications();

    // Optional: Auto-refresh notifications every 60 seconds
    setInterval(fetchNotifications, 60000);

    // ========================================
    // 🔔 Toast Notification System
    // ========================================
    
    let currentNotificationId = null;

    // Function to show toast notification
    window.showNotificationToast = function(notificationId, message) {
        currentNotificationId = notificationId;
        
        $('#toastTitle').text('New Notification');
        $('#toastMessage').text(message);
        $('#notificationToast').addClass('show');

        // Auto-hide after 10 seconds
        setTimeout(function() {
            hideToast();
        }, 10000);
    };

    // Function to hide toast
    function hideToast() {
        $('#notificationToast').removeClass('show');
        currentNotificationId = null;
    }

    // Close button click
    $('#toastClose').on('click', function() {
        hideToast();
    });

    // Dismiss button click
    $('#toastDismiss').on('click', function() {
        hideToast();
    });

    // Mark as Read button click (from toast)
    $('#toastMarkRead').on('click', function() {
        if (currentNotificationId) {
            // Show loading state
            $(this).text('⏳ Marking...').prop('disabled', true);
            
            $.ajax({
                url: '<?= base_url('notifications/mark_read') ?>/' + currentNotificationId,
                type: 'POST',
                data: {
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                },
                dataType: 'json',
                success: function(response) {
                    if (response.status === 'success') {
                        // Refresh notifications to update badge
                        fetchNotifications();
                        
                        // Hide toast
                        hideToast();
                        
                        // Show success feedback
                        console.log('Notification marked as read from toast');
                        
                        // Reset button state
                        $('#toastMarkRead').text('✓ Mark as Read').prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error marking notification as read:', error);
                    console.error('XHR Response:', xhr.responseText);
                    alert('Failed to mark notification as read. Please try again.');
                    
                    // Reset button state
                    $('#toastMarkRead').text('✓ Mark as Read').prop('disabled', false);
                }
            });
        }
    });

    // Settings Menu Toggle
    window.toggleSettingsMenu = function(event) {
        event.preventDefault();
        const submenu = document.getElementById('settingsSubmenu');
        const toggle = event.currentTarget;
        
        if (submenu) {
            submenu.classList.toggle('show');
            toggle.classList.toggle('active');
        }
    };

    // Auto-expand settings menu if on settings page
    $(document).ready(function() {
        const currentUrl = window.location.href;
        if (currentUrl.includes('/settings') || currentUrl.includes('/user-management')) {
            $('#settingsSubmenu').addClass('show');
            $('.settings-toggle').addClass('active');
        }
    });
});
</script>

</body>
</html>
