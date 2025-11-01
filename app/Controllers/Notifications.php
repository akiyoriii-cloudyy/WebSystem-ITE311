<?php

namespace App\Controllers;

use App\Models\NotificationModel;

class Notifications extends BaseController
{
    protected $notificationModel;

    public function __construct()
    {
        $this->notificationModel = new NotificationModel();
    }

    /**
     * Get notifications for the current logged-in user
     * Returns JSON response with unread count and list of notifications
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function get()
    {
        $session = session();

        // Check if user is logged in
        if (!$session->get('logged_in')) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'User not authenticated',
            ])->setStatusCode(401);
        }

        $userId = $session->get('user_id');

        // Get unread count
        $unreadCount = $this->notificationModel->getUnreadCount($userId);

        // Get latest notifications (limit 5)
        $notifications = $this->notificationModel->getNotificationsForUser($userId, 5);

        return $this->response->setJSON([
            'status'         => 'success',
            'unread_count'   => $unreadCount,
            'notifications'  => $notifications,
        ]);
    }

    /**
     * Mark a specific notification as read
     * 
     * @param int $id Notification ID
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function mark_as_read($id = null)
    {
        $session = session();

        // Check if user is logged in
        if (!$session->get('logged_in')) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'User not authenticated',
            ])->setStatusCode(401);
        }

        if (!$id) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Notification ID is required',
            ])->setStatusCode(400);
        }

        $userId = $session->get('user_id');

        // Verify the notification belongs to the current user
        $notification = $this->notificationModel->find($id);

        if (!$notification) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Notification not found',
            ])->setStatusCode(404);
        }

        if ($notification['user_id'] != $userId) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Unauthorized access',
            ])->setStatusCode(403);
        }

        // Mark as read
        $result = $this->notificationModel->markAsRead($id);

        if ($result) {
            // Get updated unread count
            $unreadCount = $this->notificationModel->getUnreadCount($userId);

            return $this->response->setJSON([
                'status'       => 'success',
                'message'      => 'Notification marked as read',
                'unread_count' => $unreadCount,
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to update notification',
            ])->setStatusCode(500);
        }
    }

    /**
     * Mark all notifications as read for the current user
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function mark_all_as_read()
    {
        $session = session();

        // Check if user is logged in
        if (!$session->get('logged_in')) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'User not authenticated',
            ])->setStatusCode(401);
        }

        $userId = $session->get('user_id');

        // Mark all as read
        $result = $this->notificationModel->markAllAsRead($userId);

        if ($result !== false) {
            return $this->response->setJSON([
                'status'       => 'success',
                'message'      => 'All notifications marked as read',
                'unread_count' => 0,
            ]);
        } else {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Failed to update notifications',
            ])->setStatusCode(500);
        }
    }
}
