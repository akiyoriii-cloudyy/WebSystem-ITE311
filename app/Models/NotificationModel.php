<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table            = 'notifications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'message', 'is_read', 'created_at'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    protected $deletedField  = 'deleted_at';

    /**
     * Get the count of unread notifications for a specific user
     * 
     * @param int $userId
     * @return int
     */
    public function getUnreadCount($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->countAllResults();
    }

    /**
     * Get notifications for a specific user (limited to latest 5)
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getNotificationsForUser($userId, $limit = 5)
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * Mark a specific notification as read
     * 
     * @param int $notificationId
     * @return bool
     */
    public function markAsRead($notificationId)
    {
        return $this->update($notificationId, ['is_read' => 1]);
    }

    /**
     * Create a new notification for a user
     * 
     * @param int $userId
     * @param string $message
     * @return int|bool
     */
    public function createNotification($userId, $message)
    {
        $userId = (int)$userId;
        $message = trim($message);
        
        // Validate inputs
        if (empty($userId) || $userId <= 0) {
            log_message('error', 'NotificationModel::createNotification: Invalid user_id: ' . $userId);
            return false;
        }
        
        if (empty($message)) {
            log_message('error', 'NotificationModel::createNotification: Empty message for user_id: ' . $userId);
            return false;
        }
        
        try {
            // Skip validation to ensure notification is always created
            $this->skipValidation(true);
            
            $data = [
                'user_id'    => $userId,
                'message'    => $message,
                'is_read'    => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            
            log_message('debug', 'NotificationModel::createNotification: Attempting to insert notification for user_id: ' . $userId . ', message: ' . substr($message, 0, 50));
            
            $result = $this->insert($data);
            
            // Re-enable validation
            $this->skipValidation(false);
            
            // In CodeIgniter 4, insert() returns the insert ID on success, or false on failure
            if ($result !== false) {
                $insertId = is_numeric($result) ? $result : $this->getInsertID();
                log_message('info', 'NotificationModel::createNotification: Successfully created notification ID: ' . $insertId . ' for user_id: ' . $userId);
                return $insertId;
            } else {
                // If model insert fails, try direct database insert
                log_message('warning', 'NotificationModel::createNotification: Model insert returned false, trying direct DB insert');
                
                $db = \Config\Database::connect();
                $builder = $db->table('notifications');
                $dbResult = $builder->insert($data);
                
                if ($dbResult) {
                    $insertId = $db->insertID();
                    log_message('info', 'NotificationModel::createNotification: Successfully created notification via direct DB insert, ID: ' . $insertId . ' for user_id: ' . $userId);
                    return $insertId;
                } else {
                    $dbError = $db->error();
                    log_message('error', 'NotificationModel::createNotification: Direct DB insert also failed. Error: ' . json_encode($dbError));
                    return false;
                }
            }
        } catch (\Exception $e) {
            // Re-enable validation even if insert fails
            $this->skipValidation(false);
            log_message('error', 'NotificationModel::createNotification failed: ' . $e->getMessage());
            log_message('error', 'NotificationModel::createNotification trace: ' . $e->getTraceAsString());
            
            // Last resort: try direct database insert
            try {
                $db = \Config\Database::connect();
                $builder = $db->table('notifications');
                $data = [
                    'user_id'    => $userId,
                    'message'    => $message,
                    'is_read'    => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                ];
                $dbResult = $builder->insert($data);
                if ($dbResult) {
                    $insertId = $db->insertID();
                    log_message('info', 'NotificationModel::createNotification: Successfully created notification via exception fallback, ID: ' . $insertId);
                    return $insertId;
                }
            } catch (\Exception $e2) {
                log_message('error', 'NotificationModel::createNotification: Exception fallback also failed: ' . $e2->getMessage());
            }
            
            return false;
        }
    }

    /**
     * Mark all notifications as read for a user
     * 
     * @param int $userId
     * @return bool
     */
    public function markAllAsRead($userId)
    {
        return $this->where('user_id', $userId)
                    ->where('is_read', 0)
                    ->set(['is_read' => 1])
                    ->update();
    }
}
