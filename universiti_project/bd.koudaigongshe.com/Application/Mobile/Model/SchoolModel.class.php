<?php
namespace Mobile\Model;
use Think\Model;
class SchoolModel extends Model {

    public function get_school(){

        $school = M('Canteen')->getBySchool_ext($this->position['school_ext']);
        $school = M('Canteen')->where(['school_ext' => $this->position['school_ext']])->select();
        p($school);

    }
    public function get_canteen_all_by_building($building){

    }

}
