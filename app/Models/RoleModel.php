<?php

namespace App\Models;

use CodeIgniter\Model;

class RoleModel extends Model
{
    protected $table = 'roles';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $allowedFields = [];

    public function get_default_role($role_type, $license_key)
    {
        $builder = $this->db->table($this->table);
        $builder->where('role_type', $role_type);
        $builder->where('license_key', $license_key);
        $builder->orderBy('id', 'ASC');
        $builder->limit(1);
        $query = $builder->get();
        
        if ($query->getNumRows() == 1) {
            return $query->getRow()->id;
        }
        
        return false;
    }

    public function get_default_patient_role($license_key)
    {
        // Similar logic for patient role
        $builder = $this->db->table($this->table);
        $builder->where('license_key', $license_key);
        $builder->where('role_type', 3); // Assuming 3 is patient role type
        $builder->orderBy('id', 'ASC');
        $builder->limit(1);
        $query = $builder->get();
        
        if ($query->getNumRows() == 1) {
            return $query->getRow()->id;
        }
        
        return false;
    }
}

