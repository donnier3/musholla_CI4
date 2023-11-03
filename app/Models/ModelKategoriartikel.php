<?php

namespace App\Models;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\Model;

class ModelKategoriartikel extends Model
{
    protected $table = 'tbl_kategoriartikel';
    protected $primaryKey = 'id_kategoriartikel';
    protected $useTimestamps = true;
    protected $allowedFields = ['nama_kategoriartikel', 'slug_kategoriartikel'];
    protected $column_order = [null, 'nama_kategoriartikel', 'slug_kategoriartikel', 'created_at', null];
    protected $column_search = ['nama_kategoriartikel'];
    protected $order = ['id_kategoriartikel' => 'ASC'];
    // protected $request;
    protected $db;
    protected $dt;

    function __construct(RequestInterface $request)
    {
        parent::__construct();
        $this->db = db_connect();
        $this->request = $request;

        $this->dt = $this->db->table($this->table)->select('*');
    }

    public function getKategoriartikel($id_kategoriartikel = false)
    {
        if ($id_kategoriartikel == false) {
            return $this->findAll();
        }
        return $this->where(['id_kategoriartikel' => $id_kategoriartikel])->first();
    }

    public function tambah($data)
    {
        $this->save($data);
    }

    public function hapus($id_kategoriartikel)
    {
        $this->where('id_kategoriartikel', $id_kategoriartikel);
        $this->delete();
    }

    private function _get_datatables_query()
    {
        $i = 0;
        foreach ($this->column_search as $item) {
            if ($this->request->getPost('search')['value']) {
                if ($i === 0) {
                    $this->dt->groupStart();
                    $this->dt->like($item, $this->request->getPost('search')['value']);
                } else {
                    $this->dt->orLike($item, $this->request->getPost('search')['value']);
                }
                if (count($this->column_search) - 1 == $i)
                    $this->dt->groupEnd();
            }
            $i++;
        }

        if ($this->request->getPost('order')) {
            $this->dt->orderBy($this->column_order[$this->request->getPost('order')['0']['column']], $this->request->getPost('order')['0']['dir']);
        } else if (isset($this->order)) {
            $order = $this->order;
            $this->dt->orderBy(key($order), $order[key($order)]);
        }
    }
    function get_datatables()
    {
        $this->_get_datatables_query();
        if ($this->request->getPost('length') != -1)
            $this->dt->limit($this->request->getPost('length'), $this->request->getPost('start'));
        $query = $this->dt->get();
        return $query->getResult();
    }
    function count_filtered()
    {
        $this->_get_datatables_query();
        return $this->dt->countAllResults();
    }
    public function count_all()
    {
        $tbl_storage = $this->db->table($this->table);
        return $tbl_storage->countAllResults();
    }
}
