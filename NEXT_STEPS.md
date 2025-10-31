# Next Steps for CodeIgniter 4 Migration

## ✅ Progress Made

1. **Base Controller Converted**: `Secure.php` - This is critical as 21 controllers extend it
2. **Core Models Converted**:
   - `PersonModel.php` (partial - needs all methods)
   - `AppointmentModel.php`
   - `RoleModel.php`
   - `ModuleModel.php`
3. **Sample Controllers Converted**:
   - `Welcome.php`
   - `Maintenance.php`
   - `Dashboard.php`
4. **Library Stub Created**: `TankAuth.php` (needs completion)

## 🚧 Critical Items to Complete

### 1. Complete TankAuth Library
The `TankAuth` library in `app/Libraries/TankAuth.php` is a stub. You need to:
- Port all methods from `application/libraries/tank_auth.php`
- Update database queries to CI4 syntax
- Update session handling
- Complete password hashing implementation

### 2. Create TankAuth Models
Create models for:
- `app/Models/TankAuth/UsersModel.php`
- `app/Models/TankAuth/LoginAttemptsModel.php`
- `app/Models/TankAuth/UserAutologinModel.php`

### 3. Complete PersonModel
The `PersonModel` only has partial methods. Add all remaining methods from `application/models/Person.php`:
- `get_doctors()`
- `get_user_by_token()`
- `get_info_doctor()`
- And any other methods used in controllers

### 4. Convert Remaining Controllers (20 remaining)
All extend `Secure`, so they should follow the `Dashboard.php` pattern:

**Controllers to convert:**
- [ ] Appointments.php
- [ ] Auth.php (CRITICAL - authentication)
- [ ] Calendar.php
- [ ] Countries.php
- [ ] Friends.php
- [ ] Invoices.php
- [ ] Locations.php
- [ ] Mails.php
- [ ] Maps.php
- [ ] Messages.php
- [ ] Notifications.php
- [ ] Patients.php
- [ ] Posts.php
- [ ] Queing.php
- [ ] Records.php
- [ ] Reports.php
- [ ] Roles.php
- [ ] Room.php
- [ ] Settings.php
- [ ] Templates.php
- [ ] User.php
- [ ] Utilities.php

**Pattern for converting Secure-based controllers:**
```php
<?php
namespace App\Controllers;

class YourController extends Secure
{
    public function index()
    {
        // Use $this->request instead of $this->input
        // Use return view() instead of $this->load->view()
        // Use $this->response->setJSON() for JSON responses
    }
}
```

### 5. Convert Remaining Models
Convert all models in `application/models/` to CI4 format:

- [ ] Appconfig.php → AppconfigModel.php
- [ ] Common.php → CommonModel.php
- [ ] Dose.php → DoseModel.php
- [ ] Media_model.php → MediaModel.php
- [ ] Medication.php → MedicationModel.php
- [ ] Patient.php → PatientModel.php
- [ ] Post.php → PostModel.php
- [ ] Que.php → QueModel.php
- [ ] Record.php → RecordModel.php
- [ ] Report.php → ReportModel.php
- [ ] Template.php → TemplateModel.php
- [ ] Vaccine.php → VaccineModel.php

### 6. Migrate Libraries
Check `application/libraries/` and convert:
- [ ] cmail library
- [ ] Any other custom libraries

### 7. Update Helper Functions
Check if any custom helpers need updates in `application/helpers/`

### 8. Fix Dashboard Controller
The `Dashboard` controller uses CI3's output library features (`set_template`, `load->section`) which don't exist in CI4. You'll need to:
- Create a custom template system, OR
- Use CI4's view layouts, OR
- Manually include header/sidebar/footer in each view

### 9. Update All Database Queries
Throughout the codebase, ensure all database queries use CI4 syntax:
- `$this->db->get()` → `$builder->get()`
- `$this->db->where()` → `$builder->where()`
- `query->num_rows()` → `query->getNumRows()`
- `query->result()` → `query->getResult()`
- `query->row()` → `query->getRow()`

### 10. Test Authentication Flow
1. User registration
2. User login
3. Session persistence
4. Logout
5. Password reset (if implemented)

## 🔍 Testing Checklist

After each conversion:
- [ ] Controller loads without errors
- [ ] Views render correctly
- [ ] Database queries work
- [ ] AJAX endpoints respond correctly
- [ ] Session handling works
- [ ] Authentication works

## 📝 Notes

1. **Template System**: CI4 doesn't have CI3's Output library with templates. You'll need to handle this differently, possibly with:
   - View layouts
   - Custom template library
   - Manual includes

2. **_remap Method**: CI4 uses routes instead of `_remap`. The `_remap` method in `Secure` controller is provided for backwards compatibility, but consider migrating to proper routes.

3. **Session**: CI4's session handling is different. Make sure session configuration matches your needs.

4. **Helpers**: Some helpers may need updating. Check `application/helpers/` for custom helpers.

5. **Config Files**: Any custom config files in `application/config/` should be moved to `app/Config/` and converted to CI4 format.

## 🎯 Priority Order

1. **Complete TankAuth library** (blocks authentication)
2. **Convert Auth controller** (needed for login)
3. **Complete PersonModel** (used by Secure and many controllers)
4. **Convert high-traffic controllers** (Dashboard, Patients, Appointments)
5. **Convert remaining models**
6. **Convert remaining controllers**
7. **Test thoroughly**
8. **Fix template/view rendering issues**

## 💡 Quick Reference

**Controller Conversion:**
- `$this->input->post()` → `$this->request->getPost()`
- `$this->input->get()` → `$this->request->getGet()`
- `$this->input->is_ajax_request()` → `$this->request->isAJAX()`
- `$this->load->view()` → `return view()`
- `echo json_encode()` → `return $this->response->setJSON()`
- `$this->session->userdata()` → `$this->session->get()`
- `$this->session->set_userdata()` → `$this->session->set()`

**Model Conversion:**
- `$this->db->from()` → `$builder = $this->db->table()`
- `$this->db->where()` → `$builder->where()`
- `$query->num_rows()` → `$query->getNumRows()`
- `$query->result()` → `$query->getResult()`
- `$query->row()` → `$query->getRow()`
- `$query->result_array()` → `$query->getResultArray()`

