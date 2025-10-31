<?php

namespace App\Models;

use CodeIgniter\Model;

class PersonModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $allowedFields = [];
    
    protected $tankAuth;
    
    public function __construct()
    {
        parent::__construct();
        $this->tankAuth = new \App\Libraries\TankAuth();
    }

    public function exists_user($id)
    {
        $builder = $this->db->table('users');
        $builder->where('users.id', $id);
        $query = $builder->get();
        
        return ($query->getNumRows() == 1);
    }

    public function exists_profile($id)
    {
        $builder = $this->db->table('users_profiles');
        $builder->join('users', 'users.id = users_profiles.user_id');
        $builder->where('users_profiles.user_id', $id);
        $query = $builder->get();
        
        return ($query->getNumRows() == 1);
    }

    public function get_all_patients($limit = 5)
    {
        $builder = $this->db->table('patients as p');
        $builder->select('up.address_1 as location, COUNT(person_id) as count');
        $builder->join('users as u', 'p.person_id=u.id');
        $builder->join('users_profiles as up', 'p.person_id=up.user_id');
        $builder->where('license_key', $this->tankAuth->get_license_key());
        $builder->orderBy('up.address_1', 'asc');
        $builder->groupBy('up.address_1');
        $builder->limit($limit);
        
        return $builder->get();
    }

    public function get_location($limit = false)
    {
        $builder = $this->db->table('users_profiles');
        $builder->select('modified as trns_date, COUNT(address_1) as counter, address_1 as location', false);
        $builder->join('users', 'users_profiles.user_id = users.id', 'left');
        $builder->where('license_key', $this->tankAuth->get_license_key());
        $builder->groupBy('address_1');
        
        if ($limit) {
            $builder->limit($limit);
        }
        
        return $builder->get();
    }

    public function count_all()
    {
        $builder = $this->db->table('users');
        return $builder->countAllResults();
    }

    public function get_user_info($id)
    {
        $builder = $this->db->table('users');
        $builder->where('id', $id);
        $query = $builder->get(1);
        
        if ($query->getNumRows() == 1) {
            return $query->getRow();
        } else {
            // Create object with empty properties
            $fields = $this->db->getFieldNames('users');
            $obj = new \stdClass();
            
            foreach ($fields as $field) {
                $obj->$field = '';
            }
            
            return $obj;
        }
    }

    public function get_email_id($email)
    {
        $builder = $this->db->table('users');
        $builder->select('id');
        $builder->where('email', $email);
        $query = $builder->get();
        
        foreach ($query->getResult() as $row) {
            return $row->id;
        }
    }

    public function get_profile_info($id)
    {
        $builder = $this->db->table('users_profiles');
        $builder->join('users', 'users.id = users_profiles.user_id');
        $builder->where('users_profiles.user_id', $id);
        $query = $builder->get();
        
        if ($query->getNumRows() == 1) {
            return $query->getRow();
        } else {
            $obj = new \stdClass();
            $fields = $this->db->getFieldNames('users_profiles');
            
            foreach ($fields as $field) {
                $obj->$field = '';
            }
            
            return $obj;
        }
    }
    
    // Add other methods from Person model as needed
    // This is a partial conversion - you'll need to add all methods from the original Person.php
}

