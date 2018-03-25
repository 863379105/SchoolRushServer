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
                'members'=>array('name'=>"members")
            ),
            'getById' => array(
                'id' => array("name" => "id")
            ),
            'deleteById' => array(
                'id' => array("name" => "id")
            )
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

        return $data;
    }

    /**
     * 根据ID获取
     * @desc 根据ID获取学校信息
     * @param int id 要获取的内容的id
     * 
     * @return data 该id指定的内容
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
     * @param int 要删除的的id
     * 
     * @return array data 删除指定id之后的内容
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
     * @param string 增加的学校名称
     * @param int   增加的学校成员数（一般为0）
     * @return data 增加的学校id
     */
    public function add() {
        $insert = array(
            'name'=>$this->name,
            'members'=>$this->members,
        );

        $model = new CampusModel();

        $id = $model->add($insert);

        return $id;
    }

}
