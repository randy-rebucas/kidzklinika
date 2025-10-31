<?php

namespace App\Models;

use CodeIgniter\Model;

class ModuleModel extends Model
{
    protected $table = 'modules';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $allowedFields = [];

    public function get_default_role($license_key)
    {
        $builder = $this->db->table($this->table);
        $builder->where('license_key', $license_key);
        $builder->orderBy('id', 'ASC');
        $builder->limit(1);
        $query = $builder->get();
        
        if ($query->getNumRows() == 1) {
            return $query->getRow()->role_id;
        }
        
        return false;
    }

    public function has_permission($module_id, $role_id, $action, $license_key)
    {
        $builder = $this->db->table('permissions');
        $builder->where('module_id', $module_id);
        $builder->where('role_id', $role_id);
        $builder->where('action', $action);
        $builder->where('license_key', $license_key);
        $query = $builder->get();
        
        return ($query->getNumRows() == 1);
    }
}

