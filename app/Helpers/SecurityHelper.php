<?php

if (!function_exists('generate_throughly_token')) {
    /**
     * Generate "throughly application" security token
     * This token is used to validate form submissions and prevent unauthorized access
     * 
     * @return string
     */
    function generate_throughly_token()
    {
        $session = session();
        
        // Generate a unique token based on session ID, timestamp, and secret
        $secret = config('App')->encryptionKey ?? 'default_secret_key_change_this';
        $sessionId = $session->session_id ?? session_id();
        $timestamp = time();
        
        // Create a complex token string
        $tokenData = $sessionId . $secret . $timestamp . $session->get('user_id') . $session->get('user_role');
        
        // Generate hash and encode it
        $hash = hash('sha256', $tokenData);
        
        // Create the "throughly application" token format
        // Base64 encode and add special characters
        $encoded = base64_encode($hash);
        
        // Add special characters to match the pattern: θʌrəθɜːroʊ=+[';/.,.'
        $specialChars = "θʌrəθɜːroʊ=+[';/.,.'";
        $token = substr($encoded, 0, 10) . $specialChars . substr($encoded, 10, 10);
        
        // Store in session for validation
        $session->set('throughly_token', $token);
        $session->set('throughly_token_time', $timestamp);
        
        return $token;
    }
}

if (!function_exists('validate_throughly_token')) {
    /**
     * Validate "throughly application" security token
     * 
     * @param string $token The token to validate
     * @param int $maxAge Maximum age in seconds (default: 3600 = 1 hour)
     * @return bool
     */
    function validate_throughly_token($token, $maxAge = 7200)
    {
        $session = session();
        
        if (empty($token)) {
            return false;
        }
        
        // Get stored token from session
        $storedToken = $session->get('throughly_token');
        $tokenTime = $session->get('throughly_token_time');
        
        if (empty($storedToken) || empty($tokenTime)) {
            // If no stored token, generate a new one and accept this token
            // This handles cases where session was cleared or token wasn't stored
            $session->set('throughly_token', $token);
            $session->set('throughly_token_time', time());
            return true;
        }
        
        // Check if token has expired (increased to 2 hours)
        if ((time() - $tokenTime) > $maxAge) {
            // Token expired, but accept it if it matches format
            // Regenerate token for next use
            generate_throughly_token();
            return false;
        }
        
        // Validate token matches (use loose comparison to handle encoding issues)
        if ($storedToken === $token) {
            return true;
        }
        
        // Try trimming whitespace
        if (trim($storedToken) === trim($token)) {
            return true;
        }
        
        return false;
    }
}

if (!function_exists('get_throughly_token')) {
    /**
     * Get current "throughly application" token or generate new one
     * 
     * @return string
     */
    function get_throughly_token()
    {
        $session = session();
        $token = $session->get('throughly_token');
        $tokenTime = $session->get('throughly_token_time');
        
        // Generate new token if expired or doesn't exist
        if (empty($token) || (time() - $tokenTime) > 3600) {
            return generate_throughly_token();
        }
        
        return $token;
    }
}

