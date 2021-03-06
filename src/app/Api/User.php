<?php
namespace App\Api;

use PhalApi\Api;
use App\Model\User as UserModel;
use App\Model\Question as QuestionModel;
use App\Model\Usertoq as UsertoqModel;
use App\Model\Major as MajorModel;
use App\Model\Campus as CampusModel;
use App\Model\Token as TokenModel;
use App\Domain\Token as TokenDomain;
use App\Common\Upload;
use App\Common\GD;
/**
 * 用户接口类
 *
 * @author: dogstar <chanzonghuang@gmail.com> 2014-10-04
 */

class User extends Api {
	public function getRules() {
        return array(
            'index' => array(
                'username' 	=> array('name' => 'username'),
            ),
            'add' => array(
                'name' => array('name' => "name"),
                'pass'=>array('name'=>"pass"),
                'identify'=>array('name'=>"identify"),
                'email'=>array('name'=>"email"),
                'tel'=>array('name'=>"tel"),
                'campusID'=>array('name'=>"campusID"),
                'majorID'=>array('name'=>"majorID"),
                'vice'=>array('name'=>"vice"),
                'avatar'=>array('name'=>"avatar"),
                'gender'=>array('name'=>"gender"),
                'describe'=>array('name'=>"describe"),
            ),
            'getById' => array(
                'id' => array("name" => "id")
            ),
            'deleteById' => array(
                'id'=> array("name" => "id")
            ),
            'updateById' => array(
                'id' => array('name' => 'id','require'=>true),
                'name' => array('name' => "name",'require'=>true),
                'pass'=>array('name'=>"pass",'require'=>true),
                'identify'=>array('name'=>"identify",'require'=>false,'default'=>null),
                'email'=>array('name'=>"email",'require'=>true),
                'tel'=>array('name'=>"tel",'require'=>true),
                'campusID'=>array('name'=>"campusID",'require'=>true),
                'majorID'=>array('name'=>"majorID",'require'=>true),
                'vice'=>array('name'=>"vice",'require'=>false,'default'=>null),
                'avatar'=>array('name'=>"avatar",'require'=>false,'default'=>null),
                'gender'=>array('name'=>"gender",'require'=>false,'default'=>null),
                'describe'=>array('name'=>"describe",'require'=>false,'default'=>null),
            ),
            'getByName'=>array(
                'name'=>array('name'=>'name'),
            ),
             'getGoodAtRank'=>array(
                 'uid'=>array('name'=>'uid'),
             ),  
             'getGoodAtRankTop'=>array(
                 'num'=>array('name'=>'num'),
                 'uid'=>array('name'=>'uid'),
             ),
            "login" => array(
                "name" => array("name" => "name"),
                "pass" => array("name" => "pass"),
            ),
            "logout" => array(
                "name" => array("name" => "name"),
            ),
            "searchByName" => array(
                "name" => array("name"=>"name"),
                "page" => array("name" => "page"),
                "num"  => array("name" => "num"),
            ),
            "getPage"   => array(
                "page" => array("name" => "page"),
                "num"  => array("name" => "num"),
            ),
            "getFilterPage" => array(
                "page"      => array("name" => "page", "require" => true),
                "num"       => array("name" => "num", "require" => true),
                "gender"    => array("name" => "gender", "default" => -1, "require" => false),
                "identify"  => array("name" => "identify", "default" => -1, "require" => false),
                "campusID"  => array("name" => "campusID", "default" => -1, "require" => false),
                "majorID"   => array("name" => "majorID", "default" => -1, "require" => false),
            )
        );
	}

    /**
     * 获取所有用户
     * @desc 获取所有用户信息
     * @return array data 获取的所有用户信息
     * 
     */
    public function getAll() {
        $model = new UserModel();
        $data = $model->getAll();

        return $data;
    }

    /**
     * 根据id获取
     * @desc 根据id获取用户信息
     * @param int id 要获取的用户id
     * @return data data 改id指定的用户信息
     */

    public function getById() {
        $userModel = new UserModel();
        $campusModel = new CampusModel();
        $majorModel = new MajorModel();
        $usertoqModel = new UsertoqModel();

        $data = $userModel->getById($this->id);
        $campusId = $data["campusID"];
        $majorId = $data["majorID"];
        
        $data["majorInfo"] = $majorModel->getById($majorId);
        $data["campusInfo"]  = $campusModel->getById($campusId);
        $data["solveInfo"]["passedNum"]   = $usertoqModel->getPassedNum($this->id);
        $data["solveInfo"]["tobeSolvedNum"]   = $usertoqModel->getTobeSolvedNum($this->id);

        return $data;
    }

    /**
     * 根据id删除
     * @desc 根据id删除用户信息
     * @param int id 要删除的用户id
     * @return int data 要删除的用户id
     */
    public function deleteById()
    {
        $model = new UserModel();
        $data = $model->deleteById($this->id);

        return $data;
    }

    /**
     * 增加用户 => 用户注册
     * @desc 增加用户信息 
     * @param string name 必须 增加的用户名称
     * @param string pass 必须 密码
     * @param int identify 身份
     * @param string email 必须 email
     * @param string tel 电话
     * @param int campusID 所在学校ID
     * @param int major 所在专业ID
     * @param int vice 副专业ID
     * @return array id 增加的用户Id false 表示用户名重复
     */

    public function add() {
        $model = new UserModel();
        
        //对用户名查重
        $isNameRepeat = $model->isRepeat("name", $this->name);
        $isMailRepeat = $model->isRepeat("email", $this->email);

        if($isNameRepeat)
            return array(
                "res" => false,
                "error" => "用户名重复"
            );
        if($isMailRepeat)
            return array(
                "res" => false,
                "error" => "邮箱重复"
            );

        if($this->identify == NULL)
            $this->identify = 2;
        
            
        $insert = array(
            'name'      => $this->name,
            'pass'      => $this->pass,
            'identify'  => $this->identify,
            'email'     => $this->email,
            'tel'       => $this->tel,
            'campusID'  => $this->campusID,
            'majorID'   => $this->majorID,
            'vice'      => $this->vice,
            'avatar'    =>  "",  //头像地址先留空 后面上传之后更新
        );
        
        $res = $model->add($insert);
        
        //生成随机头像并获取头像外链 更新用户头像外链
        $GD = new GD();
        $avatarBase64 = $GD->getUserDefaultAvatarRandom();
        $this->avatar = $GD->base64Upload($avatarBase64, $res["id"]);
        $data = array("id" => $res["id"], "avatar" => $this->avatar);
        $model->updateById($res["id"], $data);

        //在返回的数据中添加一条token
        $tokenModel = new TokenDomain();
        $tokenRes = $tokenModel->add($res["id"]);
        $res["token"] = $tokenRes["token"];
        $res["avatar"] = $this->avatar; //更新头像地址
        return $res;
    }

    /**
     * 根据名字获取id
     * @desc 根据名字获取id
     * @param string name 要获取的id的名字
     * @return int id 该名字对应的id
     */

    public function getByName()
    {
        $model = new UserModel();
        $campusModel = new CampusModel();
        $majorModel = new MajorModel();

        $data = $model->getByName($this->name);
        $campusId = $data["campusID"];
        $majorId = $data["majorID"];
        
        $majorName = $majorModel->getById($majorId);
        $majorName = $majorName["name"];
        $data["majorName"] = $majorName;

        $campusName = $campusModel->getById($campusId);
        $campusName = $campusName["name"];
        $data["campusName"] = $campusName;

        return $data;
    }

    /**
     * 根据ID更新用户信息
     * @param id 用户id
     * @param name 用户名
     * @param pass 用户密码
     * @param identify 用户身份
     * @param email 用户邮箱
     * @param tel 用户电话
     * @param campusID 用户学校ID
     * @param majorID 用户专业ID
     * @param vice 用户兴趣专业ID
     * @param describe 用户一句话介绍
     * @param avatar base64编码的图片 将会存储为图片并保存到七牛云，最后将图片外链存到数据库中
     * 
     * @return res 1: 有更改 0:无更改 false: 更新失败
     */
    public function updateById() {
        $model = new UserModel();

        $base64 = $this->avatar;
        if(substr($base64, 0, 4) == "data") {
            //有新传入的base64的图片

            $saveRes = $model->base64toImg($base64, $this->id);
            if(!$saveRes)
                return array("res"=>false, "error"=>"保存本地图片失败");
            //上传到七牛云 将avatar设置为外链地址
            $upload = new Upload();
            $upRes = $upload->uploadToQNY($saveRes["filePath"],$saveRes["fileName"]);
            if(is_array($upRes))
                return array("res"=>false, "msg"=>"图片上传失败", "error"=>$upRes["error"]);
            $this->avatar = $upRes;
        }

        $data = array(
            'id'=>$this->id,
            'name'=>$this->name,
            'pass'=>$this->pass,
            'identify'=>$this->identify,
            'email'=>$this->email,
            'tel'=>$this->tel,
            'gender'=>$this->gender,
            'campusID'=>$this->campusID,
            'majorID'=>$this->majorID,
            'vice'=>$this->vice,
            'avatar'=>$this->avatar,
            'describe'=>$this->describe,
        );

        foreach($data as $key => $val) {
            if($val == NULL){
                //如果该参数没有传的话 就从data中删除此属性
                $keys = array_keys($data);
                $index = array_search($key, $keys);

                array_splice($data, $index, 1);
            }
        }
        //TODO: 可能会因为邮箱重复而失败 待解决

        $id = $model->updateById($this->id,$data);
        return array("res"=>$id);
    }

    /**
     * 用户登陆
     * 
     * @param string name 用户名
     * @param string pass 用户密码
     * 
     * @return bool 成功信息 成功之后会返回token信息
     */
    public function login() {
        $userModel = new UserModel();

        //判断是邮箱还是用户名
        $emailPattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
        preg_match($emailPattern, $this->name, $emailValid);
        $user = "";
        if(count($emailValid) == 0) { //是用户名
            $user = $userModel->getByName($this->name);
        } else { //是邮箱
            $user = $userModel->getByMail($this->name);
        }
        if(count($user) == 0)
            return array(
                "res" => false,
                "msg" => "没有此用户"
            );
        $user = $user->fetchOne();
        //判断用户名密码是否匹配
        if ($this->pass != $user["pass"])
            return array(
                "res" => false,
                "msg" => "用户名密码不匹配"
            );
        
        //新增一条token
        $tokenModel = new TokenDomain();
        $tokenRes = $tokenModel->add($user["id"]);
        return $tokenRes;
    }
    /**
     * 获取用户擅长排行
     * @desc 获取用户擅长的题型排行
     * @param int uid 用户id
     * @return array arr 用户擅长的题型排行
     */
    public function  getGoodAtRank()
    {
        $model1=new QuestionModel();
        $model2=new UsertoqModel();
        $model3=new MajorModel();
        $pq=$model2->getPQIdByuid($this->uid);
        $tp=array();
        foreach($pq as $pqs)
        {
            $tp[]=$pqs['id'];
        }
        $model=$model1->getById($tp)->fetchAll();//获取到所有通过的问题信息
        $major=array();
        $d=0;
        for($i=0;$i<sizeof($model);$i++)//将题目专业id保存在major数组里，将对应的题目数量保存在num数组里
        {
            for($j=0;$j<sizeof($major);$j++)
            {
                if($major[$j]["id"]==$model[$i]["majorID"])
                {
                    $num[$j]++;
                    break;
                }
            }
            if($j==sizeof($major)) 
            {
                $major[]["id"]=$model[$i]["majorID"];
                $num[]=1;
            }
        }
        array_multisort($num,SORT_DESC,$major);//排序
        for($i=0;$i<sizeof($major);$i++)
        {
            $information=$model3->getNameByID($major[$i]["id"])->fetchone();
            $major[$i]["majorname"]=$information["name"];
            $major[$i]["percent"]=100*sprintf("%.2f", $num[$i]/sizeof($model));
        }
        return $major;
    }


     /**
     * 统计题目专业最多的前几个
     * @desc 输入想要获取的数量和用户id，获取通过题目的排行前几个
     * @param int uid 用户id
     * @param int num 想要获取的数量
     * @return array arr 用户擅长的题型排行前几个
     */
    public function  getGoodAtRankTop()
    {
        $model1=new QuestionModel();
        $model2=new UsertoqModel();
        $model3=new MajorModel();
        $pq=$model2->getPQIdByuid($this->uid);
        $tp=array();
        foreach($pq as $pqs)
        {
            $tp[]=$pqs['id'];
        }
        $model=$model1->getById($tp)->fetchAll();//获取到所有通过的问题信息
        $major=array();
        $d=0;
        for($i=0;$i<sizeof($model);$i++)//将题目专业id保存在major数组里，将对应的题目数量保存在num数组里
        {
            for($j=0;$j<sizeof($major);$j++)
            {
                if($major[$j]["id"]==$model[$i]["majorID"])
                {
                    $num[$j]++;
                    break;
                }
            }
            if($j==sizeof($major)) 
            {
                $major[]["id"]=$model[$i]["majorID"];
                $num[]=1;
            }
        }
        array_multisort($num,SORT_DESC,$major);//排序
        for($i=0;$i<sizeof($major);$i++)
        {
            $information=$model3->getNameByID($major[$i]["id"])->fetchone();
            $major[$i]["majorname"]=$information["name"];
            $major[$i]["percent"]=100*sprintf("%.2f", $num[$i]/sizeof($model));
        }
        return array_slice($major,0,$this->num);
    }
    /**
     * 用户登出
     * 
     * @param string name 用户名
     * 
     * @return bool 删除成功 1
     */
    public function logout() {
        $userModel = new UserModel();

        $user = $userModel->getByName($this->name);
        if(count($user) == 0)
            return array(
                "res" => false,
                "msg" => "没有此用户"
            );
        $user = $user->fetchOne();
        
        //删除token
        $tokenModel = new TokenDomain();
        $tokenRes = $tokenModel->deleteByUid($user["id"]);

        return $tokenRes;
    }
    /**
     * 用户名模糊查询
     * @author someonegirl
     * @modify iimT
     * @desc 输入字符串，获取包含该字符串的所有用户
     * @param string name 查询的用户名关键字
     * @param int page 页数
     * @param int num 每页多少条
     * @return array data 返回的数据
     */
    public function searchByName(){
        $model = new UserModel();
        $campusModel = new CampusModel();
        $majorModel = new MajorModel();

        $start = ($this->page - 1) * $this->num;
        $data = $model->getBylikenamePage($this->name, $start, $this->num);

        $res = array();
        //根据获取到的用户的学校与专业ID将学校名与专业名也加进返回的数据
        while($row = $data->fetch()) {
            $campusId = $row["campusID"];
            $majorId = $row["majorID"];
            
            $majorName = $majorModel->getById($majorId);
            $majorName = $majorName["name"];
            $row["majorName"] = $majorName;

            $campusName = $campusModel->getById($campusId);
            $campusName = $campusName["name"];
            $row["campusName"] = $campusName;

            array_push($res, $row);
        }
        return $res;
    }
    /**
     * 用户名模糊查询
     * @desc 输入字符串，获取包含该字符串的所有用户名
     * @author lxx
     * @param string name 查询的用户名
     * @return array data 返回的用户名
     */
    public function getBylikename(){
        $model=new UserModel();
        $data=$model->getBylikename($this->name);
        return $data;
    }

    /**
     * 获取用户在线数
     * @desc 获取用户在线数
     * @author lxx
     * @return 返回用户在线数量
     */
    public function getonlineNum(){
        $model=new TokenModel();
        $arr=$model->getAll();
        return sizeof($arr);
    }
    /**
     * 获取表的数据数量
     * @author ssh
     * @desc 获取表有多少数据
     * @return int 该表有多少条数据
     */
    public function getTotalNum(){
        $model = new UserModel();
        return $model->getTotalNum();
    }
    /**
     * 获取一页用户
     * @author iimT
     * @param page 第几页
     * @param num 每页几条
     * 
     * @return array 返回的数据
     */
    public function getPage() {
        $model = new UserModel();
        $campusModel = new CampusModel();
        $majorModel = new MajorModel();

        $start = ($this->page - 1) * $this->num;

        $data = $model->getByLimit($start, $this->num);
        $res = array();
        while($row = $data->fetch()) {
            $campusId = $row["campusID"];
            $majorId = $row["majorID"];
            
            $majorName = $majorModel->getById($majorId);
            $majorName = $majorName["name"];
            $row["majorName"] = $majorName;

            $campusName = $campusModel->getById($campusId);
            $campusName = $campusName["name"];
            $row["campusName"] = $campusName;

            array_push($res, $row);
        }
        return $res;
    }

    /**
     * 根据筛选条件获取一页用户
     * @desc 只能筛选男女 身份 学校 专业
     * @author iimT
     * @param page 第几页
     * @param num 一页几条
     * @param gender int 筛选男女
     * @param identify int 筛选身份
     * @param campusID int 筛选学校
     * @param majorID int 筛选专业
     * 
     * @return array 返回的数据
     * 
     * TODO: 可以将Model中的getByLimit与getFilterByLimit合并为一个方法
     * TODO: 删除获取总量接口，将总量信息放在逐页获取中
     */
    public function getFilterPage() {
        $model = new UserModel();
        $campusModel = new CampusModel();
        $majorModel = new MajorModel();
        
        $filterData = array();

        if($this->gender != -1)
            $filterData["gender"] = $this->gender;
        if($this->identify != -1)
            $filterData["identify"] = $this->identify;
        if($this->campusID != -1)
            $filterData["campusID"] = $this->campusID;
        if($this->majorID != -1)
            $filterData["majorID"] = $this->majorID;

        $start = ($this->page - 1) * $this->num;
        $data = $model->getFilterByLimit($filterData, $start, $this->num);

        //添加majorName 与 campusName字段
        $res = array();
        while($row = $data->fetch()) {
            $campusId = $row["campusID"];
            $majorId = $row["majorID"];
            
            $majorName = $majorModel->getById($majorId);
            $majorName = $majorName["name"];
            $row["majorName"] = $majorName;

            $campusName = $campusModel->getById($campusId);
            $campusName = $campusName["name"];
            $row["campusName"] = $campusName;

            array_push($res, $row);
        }

        return $res;
    }
}
