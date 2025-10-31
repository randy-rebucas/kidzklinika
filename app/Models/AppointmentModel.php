<?php

namespace App\Models;

use CodeIgniter\Model;

class AppointmentModel extends Model
{
    protected $table = 'appoinments';
    protected $primaryKey = 'app_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'schedule_date',
        'schedule_time',
        'title',
        'description',
        'patient_name',
        'license_key',
        'status',
        'doctor_note',
        'patient_note',
        'token'
    ];
    
    private $dOrder = 'asc';

    public function exists($id, $license_key)
    {
        $builder = $this->db->table($this->table);
        $builder->where($this->primaryKey, $id);
        $builder->where('license_key', $license_key);
        $query = $builder->get();
        
        return ($query->getNumRows() == 1);
    }

    public function get_all($license_key)
    {
        $builder = $this->db->table($this->table);
        $builder->select('app_id as id, title, description, CONCAT(schedule_date, \'.\', schedule_time) AS start', false);
        $builder->where('license_key', $license_key);
        $builder->orderBy($this->primaryKey, $this->dOrder);
        
        return $builder->get();
    }

    public function count_all($license_key)
    {
        $builder = $this->db->table($this->table);
        $builder->where('license_key', $license_key);
        return $builder->countAllResults();
    }

    public function get_info($id)
    {
        $builder = $this->db->table($this->table);
        $builder->where($this->primaryKey, $id);
        $query = $builder->get();
        
        if ($query->getNumRows() == 1) {
            return $query->getRow();
        } else {
            $obj = new \stdClass();
            $fields = $this->db->getFieldNames($this->table);
            
            foreach ($fields as $field) {
                $obj->$field = '';
            }
            
            return $obj;
        }
    }

    public function save($appointment_data, $license_key, $patient_id)
    {
        $builder = $this->db->table($this->table);
        $appointment_data['license_key'] = $license_key;
        $appointment_data['person_id'] = $patient_id;
        
        return $builder->insert($appointment_data);
    }

    public function save_info($data, $id = false)
    {
        $builder = $this->db->table($this->table);
        
        if ($id === false) {
            return $builder->insert($data);
        } else {
            $builder->where($this->primaryKey, $id);
            return $builder->update($data);
        }
    }

    public function delete_info($id)
    {
        $builder = $this->db->table($this->table);
        $builder->where($this->primaryKey, $id);
        return $builder->delete();
    }
}

