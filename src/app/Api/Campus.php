<?php
namespace App\Api;

use PhalApi\Api;
use App\Model\Campus as CampusModel;
/**
 * 学校接口类
 *
 * @author: dogstar <chanzonghuang@gmail.com> 2014-10-04
 */

class Campus extends Api {

	public function getRules() {
        return array(
            'index' => array(
                'username' 	=> array('name' => 'username'),
            ),
            'add' => array(
                'name' => array('name' => "name"),
                'members'=>array('name'=>"members"),
                'badge' => array('name' => "badge"),
                'locate' => array('name' => "locate"),
            ),
            'getById' => array(
                'id' => array("name" => "id")
            ),
            'deleteById' => array(
                'id' => array("name" => "id")
            ),
            'updateById' => array(
                'id' => array('name'=> 'id','require'=>true),
                'name' => array('name' => "name",'require'=>true),
                'members'=>array('name'=>"members",'default'=>null,'require'=>false),
                'badge' => array('name' => "badge",'default'=>null,'require'=>false),
                'locate' => array('name' => "locate",'default'=>null,'require'=>false)
            ),
            'getIdByName'=>array(
                'id'=>array('name'=>"id"),
            ),
            'getmembersById'=>array(
                'id'=>array('name'=>'id'),
            ),
        );
	}
	
	/**
	 * 默认接口服务
     * @desc 默认接口服务，当未指定接口服务时执行此接口服务
	 * @return string title 标题
	 * @return string content 内容
	 * @return string version 版本，格式：X.X.X
	 * @return int time 当前时间戳
	 */
	public function index() {
        return array(
            'title' => 'Hello ' . $this->username,
            'version' => PHALAPI_VERSION,
            'time' => $_SERVER['REQUEST_TIME'],
        );
    }

    /**
     * 获取所有内容
     * @desc 获取所有学校信息
     * 
     * @return array data 所有学校
     * 
     */
    public function getAll() {
        $model = new CampusModel();
        $data = $model->getAll();
        //循环每一行 添加label与value
        $res = array();
        while ($row = $data->fetch()) {
            $row["value"] = $row["id"];
            $row["label"] = $row["name"];
            array_push($res, $row);
        }
        return $res;
    }

    /**
     * 根据ID获取
     * @desc 根据ID获取学校信息
     * @param int id 要获取的内容的id
     * 
     * @return data id 该id指定的内容
     */
    public function getById() {
        $model = new CampusModel();
        $data = $model->getById($this->id);

        return $data;
    }

    /**
     * 根据ID删除
     * @desc 根据ID删除学校信息
     * 
     * @param int id 要删除的的id
     * 
     * @return int data 删除的id
     */
    public function deleteById(){
        $model =new CampusModel();
        $data  =$model->deleteById($this->id);

        return $data;
    }

    /**
     * 增加学校信息
     * @desc 增加学校的信息
     * 
     * @param string name 学校名称
     * @param int members 学校成员数（一般为0）
     * @param string badge 校徽图片地址
     * @param string locate 学校省份地址
     * 
     * @return array id 增加的学校的标签
     */
    public function add() {
        $insert = array(
            'name'=>$this->name,
            'members'=>$this->members,
            'badge' => $this->badge,
            'locate' => $this->locate,
        );

        $model = new CampusModel();

        $id = $model->add($insert);

        return $id;
    }

        /**
     * 根据名字获取id
     * @desc 根据名字获取id
     * @param string name 要获取的id的名字
     * @return int id 该名字对应的id
     */

    public function getIdByName()
    {
       $model = new CampusModel();
       $data = $model->getIdByName($this->name);

       return $data;
    }

    /**
     * 更新学校信息
     * @desc 根据id更新学校的信息
     * 
     * @param string name 学校名称
     * @param int members 学校成员数（一般为0）
     * @param string badge 校徽图片地址
     * @param string locate 学校省份地址
     * 
     * @return data id 更新后的学校的信息
     */
     
    public function updateById() {
        $model = new CampusModel();

        $data = array(
            "id" => $this->id,
            "name" => $this->name,
            "members" => $this->members,
            "badge" => $this->badge,
            "locate" => $this->locate,
        );

        foreach($data as $key => $val) {
            if($val == NULL){
                $keys = array_keys($data);
                $index = array_search($key, $keys);

                array_splice($data, $index, 1);
            }
        }

        $id = $model->updateById($this->id,$data);
        return array("res"=>$id);

    }
    public function getMembersById()
    {
        $model=new CampusModel();
        return $model->getmembersById($this->id);
    }
    /**
     * 获取学校总数
     */
    public function getTotalNum(){
        $model = new CampusModel();
        return $model->getTotalNum();
    }
}

