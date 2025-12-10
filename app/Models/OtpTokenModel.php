<?php

namespace App\Models;

use CodeIgniter\Model;

class OtpTokenModel extends Model
{
    protected $table            = 'otp_tokens';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = false; // Allow direct inserts for OTP generation
    protected $allowedFields    = ['user_id', 'otp_code', 'email', 'expires_at', 'is_used'];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;

    protected array $casts = [];
    protected array $castHandlers = [];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = null;
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'user_id'    => 'required|integer',
        'otp_code'   => 'required|exact_length[6]|numeric',
        'email'      => 'required|valid_email',
        'expires_at' => 'required|valid_date',
    ];
    protected $validationMessages   = [];
    protected $skipValidation       = true; // Skip validation for programmatic inserts (OTP generation)
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Generate and save OTP for user
     */
    public function generateOtp($userId, $email)
    {
        try {
            $db = \Config\Database::connect();
            
            // Generate 6-digit OTP
            $otpCode = str_pad((string)rand(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Set expiration to 10 minutes from now
            $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            // Invalidate any existing unused OTPs for this user
            try {
                $db->table('otp_tokens')
                   ->where('user_id', $userId)
                   ->where('is_used', 0)
                   ->update(['is_used' => 1]);
            } catch (\Exception $e) {
                // Ignore if no records to update
                log_message('debug', 'No existing OTPs to invalidate: ' . $e->getMessage());
            }

            // Create new OTP using database builder directly
            $data = [
                'user_id'    => (int)$userId,
                'otp_code'   => $otpCode,
                'email'      => $email,
                'expires_at' => $expiresAt,
                'is_used'    => 0,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            // Insert OTP using raw SQL query for reliability
            $sql = "INSERT INTO otp_tokens (user_id, otp_code, email, expires_at, is_used, created_at) VALUES (?, ?, ?, ?, ?, ?)";
            $result = $db->query($sql, [
                (int)$userId,
                $otpCode,
                $email,
                $expiresAt,
                0,
                date('Y-m-d H:i:s')
            ]);

            if ($result) {
                return $otpCode;
            } else {
                $error = $db->error();
                log_message('error', 'OTP insert failed. Database error: ' . json_encode($error));
                return false;
            }
        } catch (\Exception $e) {
            log_message('error', 'OTP generation error: ' . $e->getMessage());
            log_message('error', 'Stack trace: ' . $e->getTraceAsString());
            return false;
        }
    }

    /**
     * Verify OTP code
     */
    public function verifyOtp($userId, $otpCode)
    {
        $otp = $this->where('user_id', $userId)
                    ->where('otp_code', $otpCode)
                    ->where('is_used', 0)
                    ->where('expires_at >', date('Y-m-d H:i:s'))
                    ->first();

        if ($otp) {
            // Mark as used
            $this->update($otp['id'], ['is_used' => 1]);
            return true;
        }

        return false;
    }

    /**
     * Clean up expired OTPs
     */
    public function cleanupExpiredOtps()
    {
        $this->where('expires_at <', date('Y-m-d H:i:s'))
             ->orWhere('is_used', 1)
             ->delete();
    }
}
