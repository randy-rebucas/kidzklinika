<?php

namespace App\Controllers;

use App\Models\PersonModel;
use App\Models\AppointmentModel;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Welcome extends BaseController
{
    protected $session;
    protected $personModel;
    protected $appointmentModel;
    protected $cmail;

    public function initController(RequestInterface $request, ResponseInterface $response, $logger = null)
    {
        parent::initController($request, $response, $logger);
        
        $this->session = \Config\Services::session();
        $this->personModel = new PersonModel();
        $this->appointmentModel = new AppointmentModel();
        $this->cmail = new \App\Libraries\Cmail(); // Adjust based on your library location
    }

    public function index()
    {
        return view('corporate');
    }

    public function get_doctors()
    {
        $doctors = $this->personModel->get_doctors();
        return $this->response->setJSON($doctors->getResultArray());
    }

    public function confirm()
    {
        $schedule = $this->session->get('schedule');
        $doctor = $this->session->get('doctor_details');
        $patient = $this->session->get('patient_details');
        
        $err = explode('/', $schedule['theDate']);
        $token = date('Ymd');
        $appointment_data = [
            'schedule_date'   => $err[2] . '-' . $err[0] . '-' . $err[1],
            'schedule_time'   => $schedule['theTime'],
            'description'     => $schedule['theDescription'],
            'title'           => $schedule['theTitle'],
            'patient_name'    => $patient['firstname'] . ', ' . $patient['lastname'],
            'license_key'     => $doctor['license_key'],
            'status'          => 'Pending',
            'doctor_note'     => '',
            'patient_note'    => '',
            'token'           => $token
        ];
        
        if ($this->appointmentModel->save($appointment_data, $doctor['license_key'], $patient['id'])) {
            $content = html_entity_decode(html_entity_decode(view('email/appointment-html', [])));
            $config = config('App');
            $html = str_replace([
                "{{sitename}}",
                "{{app_url}}",
                "{{pid}}",
                "{{paddress}}",
                "{{pavatar}}",
                "{{pcontact}}",
                "{{pfirstname}}",
                "{{plastname}}",
                "{{daddress}}",
                "{{davatar}}",
                "{{dcontact}}",
                "{{dfirstname}}",
                "{{dlastname}}",
                "{{demail}}",
                "{{theDate}}",
                "{{theDescription}}",
                "{{theTime}}",
                "{{theTitle}}",
                "{{appointment_token}}",
            ], [
                config('TankAuth')->website_name,
                site_url(),
                $patient['id'],
                $patient['address'],
                $patient['avatar'],
                $patient['contact'],
                $patient['firstname'],
                $patient['lastname'],
                $doctor['address'],
                $doctor['avatar'],
                $doctor['contact'],
                $doctor['firstname'],
                $doctor['lastname'],
                $doctor['email'],
                $schedule['theDate'],
                $schedule['theDescription'],
                $schedule['theTime'],
                $schedule['theTitle'],
                $token
            ], $content);
            $email_content = html_entity_decode(html_entity_decode($html));

            $args = [
                'email'     => $doctor['email'],
                'firstname' => $patient['firstname'],
                'lastname'  => $patient['lastname']
            ];

            if ($this->cmail->send('appointment', $args, $email_content)) {
                $this->clear_schedule();
                $this->clear_doctor();
                $this->clear_patient();
                $this->clear_option();
                
                return $this->response->setJSON(['status' => true, 'msg' => 'Success! We well send information to your doctor notifying your appointment.']);
            } else {
                return $this->response->setJSON(['status' => false, 'msg' => 'Unable to send message! But the doctor still see your appointment on this dashboard.']);
            }
        } else {
            return $this->response->setJSON(['status' => false, 'msg' => 'Ooopppsss!!! Sorry we cannot set your appointment at this momment!']);
        }
    }

    public function get_schedule()
    {
        $this->clear_schedule();

        if (!$this->session->get('schedule')) {
            $details = [
                'theTitle'       => $this->request->getPost('theTitle'),
                'theDescription' => $this->request->getPost('theDescription'),
                'theTime'        => $this->request->getPost('theTime'),
                'theDate'        => $this->request->getPost('theDate')
            ];
            
            $this->set_schedule($details);
        }
        return $this->response->setJSON($this->session->get('schedule'));
    }

    protected function set_schedule($details)
    {
        $this->session->set('schedule', $details);
    }

    protected function clear_schedule()
    {
        return $this->session->remove('schedule');
    }

    public function get_info_doctor()
    {
        $this->clear_doctor();
        
        $row = $this->personModel->get_info_doctor($this->request->getPost('id'));
        
        if (!$this->session->get('doctor_details')) {
            $details = [
                'firstname'   => $row->firstname,
                'lastname'    => $row->lastname,
                'address'     => $row->address,
                'contact'     => $row->mobile,
                'id'          => $row->id,
                'avatar'      => $row->avatar,
                'license_key' => $row->license_key,
                'email'       => $row->email,
            ];
            
            $this->set_doctor($details);
        }
        return $this->response->setJSON($this->session->get('doctor_details'));
    }

    public function set_doctor($details)
    {
        $this->session->set('doctor_details', $details);
    }

    protected function clear_doctor()
    {
        return $this->session->remove('doctor_details');
    }

    public function get_info_patient()
    {
        $this->clear_patient();
        
        $row = $this->personModel->get_user_by_token($this->request->getPost('patient_token'));
        
        if (!$this->session->get('patient_details')) {
            $details = [
                'firstname'   => $row->firstname,
                'lastname'    => $row->lastname,
                'address'     => $row->address,
                'contact'     => $row->mobile,
                'id'          => $row->id,
                'avatar'      => $row->avatar,
                'license_key' => $row->license_key,
            ];
            $this->set_patient($details);
        }
        return $this->response->setJSON($this->session->get('patient_details'));
    }

    public function get_option()
    {
        $this->clear_option();
        
        if (!$this->session->get('option')) {
            $details = $this->request->getPost('option') ? $this->request->getPost('option') : '';
            $this->set_option($details);
        }
        return $this->response->setJSON($this->session->get('option'));
    }

    protected function set_option($option)
    {
        $this->session->set('option', $option);
    }

    protected function clear_option()
    {
        return $this->session->remove('option');
    }

    public function get_patient()
    {
        if (!$this->session->get('patient_details')) {
            $details = [
                'firstname'   => $this->request->getPost('firstname'),
                'lastname'    => $this->request->getPost('lastname'),
                'address'     => $this->request->getPost('address'),
                'contact'     => $this->request->getPost('contact'),
                'id'          => $this->request->getPost('id'),
                'avatar'      => '',
                'license_key' => ''
            ];
            $this->set_patient($details);
        }
        return $this->response->setJSON($this->session->get('patient_details'));
    }

    protected function set_patient($details)
    {
        $this->session->set('patient_details', $details);
    }

    protected function clear_patient()
    {
        return $this->session->remove('patient_details');
    }
}

