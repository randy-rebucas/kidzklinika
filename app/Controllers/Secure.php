<?php

namespace App\Controllers;

use App\Models\PersonModel;
use App\Models\RoleModel;
use App\Models\ModuleModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\RedirectResponse;

class Secure extends BaseController
{
    public $license_id;
    public $role_id;
    public $admin_role_id;
    public $patient_role_id;
    public $user_id;
    public $is_login;
    public $is_ajax;
    public $content;
    
    protected $session;
    protected $tankAuth;
    protected $personModel;
    protected $roleModel;
    protected $moduleModel;
    protected $request;
    protected $response;
    
    public function initController(RequestInterface $request, ResponseInterface $response, $logger = null)
    {
        parent::initController($request, $response, $logger);
        
        $this->session = \Config\Services::session();
        $this->request = $request;
        $this->response = $response;
        
        // Load tank_auth library (will need to be converted for CI4)
        $this->tankAuth = new \App\Libraries\TankAuth();
        $this->personModel = new PersonModel();
        $this->roleModel = new RoleModel();
        $this->moduleModel = new ModuleModel();
        
        $module_id = uri_string();
        // $this->permission_check($module_id, 'view');
        
        $this->role_id = $this->tankAuth->get_role_id();
        $this->license_id = $this->tankAuth->get_license_key();
        $this->user_id = $this->tankAuth->get_user_id();
        $this->is_login = $this->tankAuth->is_logged_in();
        $this->is_ajax = $this->request->isAJAX();
        
        // Get subscription information
        $data['user_info'] = $this->personModel->get_profile_info($this->user_id);
        
        $this->admin_role_id = $this->roleModel->get_default_role(2, $this->license_id);
        $this->patient_role_id = $this->roleModel->get_default_patient_role($this->license_id);
        
        if (!$this->is_login) {
            $this->session->set('currentUrl', current_url());
            
            $last_url = (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) 
                ? $_SERVER['HTTP_REFERER'] 
                : base_url();
            
            $this->session->set('referrer', $last_url);
            
            if (!$this->is_ajax) {
                return redirect()->to('auth/login');
            } else {
                return $this->response->setBody('<script>window.location = "' . base_url() . 'auth/login";</script>');
            }
        }
    }
    
    public function check_subscription($sUserInfo)
    {
        if ($sUserInfo->subscription_id != 1) {
            $seconds = strtotime($sUserInfo->expiration_date) - time();
            if ($seconds != 0) {
                $days = floor($seconds / 86400);
                $seconds %= 86400;
                
                $hours = floor($seconds / 3600);
                $seconds %= 3600;
                
                $minutes = floor($seconds / 60);
                $seconds %= 60;
            } else {
                $days = 0;
            }
            
            return $days;
        }
    }
    
    public function permission_check($module_id, $action)
    {
        if ($this->role_id != $this->moduleModel->get_default_role($this->license_id)) {
            if (!$this->moduleModel->has_permission($module_id, $this->role_id, $action, $this->license_id) == false) {
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }
    
    public function display_error_log($directory, $class_name, $method)
    {
        $page = "'" . $directory . "\\" . $class_name . "\\" . $method . "' is not found";
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound($page);
    }
    
    public function _remove_duplicate_cookies()
    {
        // PHP < 5.3 doesn't have header remove so this function will fatal error otherwise
        if (function_exists('header_remove')) {
            $config = config('Session');
            
            // Clean up all the cookies that are set...
            $headers = headers_list();
            $cookies_to_output = [];
            $header_session_cookie = '';
            $session_cookie_name = $config->cookieName;
            
            foreach ($headers as $header) {
                list($header_type, $data) = explode(':', $header, 2);
                $header_type = trim($header_type);
                $data = trim($data);
                
                if (strtolower($header_type) == 'set-cookie') {
                    header_remove('Set-Cookie');
                    
                    $cookie_value = current(explode(';', $data));
                    list($key, $val) = explode('=', $cookie_value);
                    $key = trim($key);
                    
                    if ($key == $session_cookie_name) {
                        // OVERWRITE IT (yes! do it!)
                        $header_session_cookie = $data;
                        continue;
                    } else {
                        // Not a session related cookie, add it as normal
                        $cookies_to_output[] = ['header_type' => $header_type, 'data' => $data];
                    }
                }
            }
            
            if (!empty($header_session_cookie)) {
                $cookies_to_output[] = ['header_type' => 'Set-Cookie', 'data' => $header_session_cookie];
            }
            
            foreach ($cookies_to_output as $cookie) {
                header("{$cookie['header_type']}: {$cookie['data']}", false);
            }
        }
    }
    
    public function setup()
    {
        return view('ajax/setup');
    }
    
    /**
     * Facebook initialize config
     *
     * @since       1.0.1 First time this was introduced.
     * @return      object
     */
    public function fb_init()
    {
        $this->fb = new \Facebook\Facebook([
            'app_id' => '224864781343525',
            'app_secret' => '20498c87e89a9d794df97cdac8542192',
            'default_graph_version' => 'v2.9',
        ]);
        
        return $this->fb;
    }
    
    /**
     * Handle _remap functionality for CI4
     * CI4 uses routes instead of _remap, but this method can be used
     * for backwards compatibility
     */
    public function _remap($method, ...$params)
    {
        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $params);
        }
        
        $directory = getcwd();
        $class_name = get_class($this);
        $this->display_error_log($directory, $class_name, $method);
    }
}

