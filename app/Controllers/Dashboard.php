<?php

namespace App\Controllers;

class Dashboard extends Secure
{
    public function __construct()
    {
        parent::initController(
            \Config\Services::request(),
            \Config\Services::response(),
            \Config\Services::logger()
        );
    }

    public function _remap($method, ...$params)
    {
        if (method_exists($this, $method)) {
            return call_user_func_array([$this, $method], $params);
        }

        $directory = getcwd();
        $class_name = get_class($this);
        $this->display_error_log($directory, $class_name, $method);
    }

    private function _init()
    {
        // CI4 doesn't have output library templates in the same way
        // You'll need to handle template rendering differently
        // For now, this is a placeholder - adjust based on your template system
    }

    public function index()
    {
        if ($this->request->isAJAX()) {
            $data['module'] = get_class($this);
            return view('ajax/dashboard', $data);
        } else {
            $this->_init();
            // Handle meta tags differently in CI4
            return view('dashboard/index', $data ?? []);
        }
    }
}

