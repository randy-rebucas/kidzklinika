<?php

namespace App\Libraries;

use App\Models\TankAuth\UsersModel;
use Config\Services;

/**
 * Tank_auth Library for CodeIgniter 4
 * 
 * This is a partial conversion from CI3 to CI4.
 * The library needs to be fully adapted for CI4's architecture.
 */
class TankAuth
{
    private $error = [];
    private $session;
    private $usersModel;
    
    public function __construct()
    {
        $this->session = Services::session();
        $this->usersModel = new UsersModel();
        
        // Try to autologin
        $this->autologin();
    }
    
    /**
     * Get user ID from session
     */
    public function get_user_id()
    {
        return $this->session->get('user_id');
    }
    
    /**
     * Get role ID from session
     */
    public function get_role_id()
    {
        return $this->session->get('role_id');
    }
    
    /**
     * Get license key from session
     */
    public function get_license_key()
    {
        return $this->session->get('license_key');
    }
    
    /**
     * Check if user is logged in
     */
    public function is_logged_in()
    {
        return $this->session->get('user_id') !== null;
    }
    
    /**
     * Login user
     * 
     * @param string $login Username or email
     * @param string $password Password
     * @param bool $remember Remember login
     * @param bool $isSocial Is social login
     * @return bool
     */
    public function login($login, $password, $remember = false, $isSocial = false)
    {
        if ((strlen($login) > 0) && (strlen($password) > 0)) {
            $user = $this->usersModel->get_user_by_email($login);
            
            if ($user !== null) {
                if (!$isSocial) {
                    // Check password (using phpass or similar)
                    if ($this->checkPoint($password, $user->password)) {
                        $this->session->set([
                            'user_id'    => $user->id,
                            'username'   => $user->username,
                            'role_id'    => $user->role_id,
                            'license_key' => $user->license_key,
                            'status'     => ($user->activated == 1) ? 1 : 0,
                        ]);
                        
                        if ($user->activated == 0) {
                            $this->error = ['not_activated' => 'not activated'];
                            return false;
                        } else {
                            // Update login info
                            $this->usersModel->update_login_info(
                                $user->id,
                                true, // record IP
                                true  // record time
                            );
                            
                            $this->update_status($user->id, 1);
                            return true;
                        }
                    } else {
                        $this->error = ['password' => 'auth_incorrect_password'];
                        return false;
                    }
                } else {
                    // Social login
                    $this->session->set([
                        'user_id'    => $user->id,
                        'username'   => $user->username,
                        'role_id'    => $user->role_id,
                        'license_key' => $user->license_key,
                        'status'     => 1,
                    ]);
                    
                    $this->usersModel->update_login_info($user->id, true, true);
                    $this->update_status($user->id, 1);
                    return true;
                }
            } else {
                $this->error = ['login' => 'auth_incorrect_login'];
                return false;
            }
        }
        
        return false;
    }
    
    /**
     * Logout user
     */
    public function logout()
    {
        $user_id = $this->session->get('user_id');
        if ($user_id !== null) {
            $this->update_status($user_id, 0);
        }
        
        $this->session->destroy();
        return true;
    }
    
    /**
     * Check password
     * 
     * @param string $password Plain password
     * @param string $hashedPassword Hashed password
     * @return bool
     */
    private function checkPoint($password, $hashedPassword)
    {
        // Use phpass or password_verify
        require_once(APPPATH . '../application/libraries/phpass-0.1/PasswordHash.php');
        $hasher = new \PasswordHash(8, FALSE);
        return $hasher->CheckPassword($password, $hashedPassword);
    }
    
    /**
     * Update user status
     */
    private function update_status($user_id, $status)
    {
        // Update user online status
        // Implement based on your database structure
    }
    
    /**
     * Autologin
     */
    private function autologin()
    {
        // Implement autologin logic
    }
    
    /**
     * Get error messages
     */
    public function get_error()
    {
        return $this->error;
    }
    
    // Add other methods from original tank_auth library as needed
}

