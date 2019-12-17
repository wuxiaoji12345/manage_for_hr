<?php
namespace app\index\model;

use think\Model;

class Base extends Model
{
    protected $table;

    protected $pk ;

    public function add($data = [])
    {
        if(empty($data))
            return 0;

        // return $this->isUpdate(false)->save($data);

    }

    public function edit($data=[],$where=[])
    {

        if(empty($data) || empty($where))
            return 0;

        return $this->save($data,$where);
    }


    public function addAll($data = [])
    {
        if(empty($data))
            return 0;

        $inserted_data = $this->saveAll($data,false);

        // if(count($data) == count($inserted_data))
        return count($inserted_data);
    }

    //  必须传id
    public function updateAll($data = [])
    {
        if(empty($data))
            return 0;

        $update_data = $this->saveAll($data);

        return count($update_data);
    }


    //  edit
    public function editData($update=[],$condtion=[])
    {
        if(empty($update) || empty($condtion))
            return 0;

        $where = $this->getCondition($condtion);

        if(empty($where))
            return 0;

        return self::where($where)->update($update);

    }


    public function delData($condtion=[])
    {

        if(empty($condtion))
            return 0;

        return self::where($condtion)->delete();

    }


    public function getInfoById($id=0)
    {
        $id = intval($id);

        if($id<1)
            return [];
        return $this->get($id);
    }


    public function isEmpty()
    {
        return empty($this->items);
    }

}