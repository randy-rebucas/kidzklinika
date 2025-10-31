# CodeIgniter 3 to CodeIgniter 4 Upgrade Guide

This document outlines the upgrade from CodeIgniter 3.0.0 to CodeIgniter 4.6.3.

## ✅ Completed Steps

1. **CodeIgniter 4 Installation**: CodeIgniter 4.6.3 has been installed via Composer
2. **Directory Structure**: 
   - Created `app/` directory (CI4 application folder)
   - Created `writable/` directory for logs, cache, sessions, uploads
   - Copied views from `application/views/` to `app/Views/`
   - Copied models from `application/models/` to `app/Models/`

3. **Configuration Files**:
   - Updated `index.php` for CI4 bootstrap
   - Created `.env` file with database configuration
   - Updated `app/Config/App.php` (removed index_page)
   - Updated `app/Config/Database.php` (utf8 charset)
   - Updated `app/Config/Paths.php` (system directory path)

4. **Routes**: Migrated routes from CI3 to CI4 format in `app/Config/Routes.php`

5. **Sample Controllers**: 
   - Converted `Welcome.php` to CI4 format
   - Converted `Maintenance.php` to CI4 format

## ⚠️ Remaining Tasks

### 1. Convert All Controllers to CI4 Format

**Pattern for Controller Conversion:**

**CI3 Format:**
```php
<?php
class Welcome extends CI_Controller {
    function __construct() {
        parent::__construct();
        $this->load->helper('url');
        $this->load->model('Person');
    }
    
    public function index() {
        $data = $this->Person->get_all();
        $this->load->view('welcome', $data);
    }
}
```

**CI4 Format:**
```php
<?php
namespace App\Controllers;

use App\Models\PersonModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Welcome extends BaseController {
    protected $personModel;
    
    public function initController(RequestInterface $request, ResponseInterface $response, $logger = null)
    {
        parent::initController($request, $response, $logger);
        $this->personModel = new PersonModel();
    }
    
    public function index() {
        $data['records'] = $this->personModel->get_all();
        return view('welcome', $data);
    }
}
```

**Key Changes:**
- Add `namespace App\Controllers;`
- Extend `BaseController` instead of `CI_Controller`
- Use `initController()` instead of `__construct()` for initialization
- Use `$this->request` instead of `$this->input`
- Use `$this->response->setJSON()` instead of `echo json_encode()`
- Use `view()` helper function instead of `$this->load->view()`
- Use `$this->session->get()` instead of `$this->session->userdata()`
- Use `$this->session->set()` instead of `$this->session->set_userdata()`
- Use `return view()` instead of `$this->load->view()`

### 2. Convert Models to CI4 Format

**Pattern for Model Conversion:**

**CI3 Format:**
```php
<?php
class Person extends CI_Model {
    public function get_all() {
        return $this->db->get('persons')->result();
    }
}
```

**CI4 Format:**
```php
<?php
namespace App\Models;

use CodeIgniter\Model;

class PersonModel extends Model {
    protected $table = 'persons';
    protected $primaryKey = 'id';
    protected $allowedFields = ['name', 'email'];
    
    public function get_all() {
        return $this->findAll();
    }
}
```

**Key Changes:**
- Add `namespace App\Models;`
- Extend `CodeIgniter\Model` instead of `CI_Model`
- Model name should end with "Model" suffix
- Use `$this->findAll()`, `$this->find($id)`, `$this->save()`, etc.
- Define `$table`, `$primaryKey`, `$allowedFields`

### 3. Update Database Query Builder

**CI3:**
```php
$this->db->where('id', $id);
$this->db->get('table')->result();
```

**CI4:**
```php
$this->db->table('table')
    ->where('id', $id)
    ->get()
    ->getResult();
```

**Or using Model:**
```php
$this->model->where('id', $id)->first();
```

### 4. Update Libraries

Libraries need to be moved to `app/Libraries/` and updated:
- Use namespace `App\Libraries`
- Update any CI3-specific method calls

### 5. Update Helpers

Helpers are automatically loaded. Custom helpers should be in `app/Helpers/`.

### 6. Session Configuration

Update session handling:
- CI4 uses different session drivers
- Update `app/Config/Session.php` or use `.env` file
- Session database table structure may need updating

### 7. Authentication Library (tank_auth)

The tank_auth library will need significant updates:
- Check if there's a CI4 compatible version
- Or rewrite authentication logic using CI4's built-in features

### 8. Test Each Controller

Go through each controller and:
1. Update class declaration and namespace
2. Convert `$this->input` to `$this->request`
3. Convert `$this->load->view()` to `return view()`
4. Convert `$this->load->model()` to `new ModelName()`
5. Convert session methods
6. Convert JSON responses
7. Test each method

## 📋 Controllers to Convert

Based on your application, these controllers need conversion:
- Appointments.php
- Auth.php
- Calendar.php
- Countries.php
- Dashboard.php
- Friends.php
- Invoices.php
- Locations.php
- Mails.php
- Maps.php
- Messages.php
- Notifications.php
- Patients.php
- Posts.php
- Queing.php
- Records.php
- Reports.php
- Roles.php
- Room.php
- Secure.php (base controller - needs special attention)
- Settings.php
- Templates.php
- User.php
- Utilities.php

## 📋 Models to Convert

- Appconfig.php
- Appointment.php
- Common.php
- Dose.php
- Media_model.php
- Medication.php
- Module.php
- Patient.php
- Person.php
- Post.php
- Que.php
- Record.php
- Report.php
- Role.php
- Template.php
- Vaccine.php
- tank_auth/Users.php
- tank_auth/Login_attempts.php
- tank_auth/User_autologin.php

## 🔧 Configuration Updates Needed

1. **Session Configuration**: Update for CI4 session handling
2. **Email Configuration**: Update email settings in `app/Config/Email.php`
3. **Tank Auth Config**: Migrate tank_auth configuration
4. **Custom Config Files**: Any custom config in `application/config/` needs updating

## ⚠️ Important Notes

1. **PHP Version**: CI4 requires PHP 8.1+. Ensure your server meets this requirement.

2. **Old Files**: The old `application/` and `system/` folders are kept for reference. Remove them once migration is complete.

3. **Database**: The database structure should remain the same, but query syntax needs updating.

4. **URL Structure**: CI4 routing is more flexible. Review all routes.

5. **Security**: Review CSRF protection settings in `app/Config/Security.php`

6. **Testing**: Test thoroughly before deploying to production.

## 🚀 Next Steps

1. Start converting controllers one by one, starting with the most important ones (Auth, Dashboard, etc.)
2. Convert models as you encounter them
3. Update any custom libraries
4. Test each feature after conversion
5. Update any JavaScript/AJAX calls that may be affected by routing changes

## 📚 Resources

- [CodeIgniter 4 User Guide](https://codeigniter.com/user_guide/)
- [Upgrading from CodeIgniter 3](https://codeigniter.com/user_guide/installation/upgrading.html)
- [CodeIgniter 4 Migration Guide](https://codeigniter.com/user_guide/installation/upgrade_400.html)

