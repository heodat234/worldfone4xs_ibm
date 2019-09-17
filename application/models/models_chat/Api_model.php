<?php
class Api_model extends CI_Model {

    public function __construct()
    {
        $this->load->library('mongo_db');
    }
    public function get_key($secret ) {
       $this->load->helper('url');
        return $this->mongo_db->where(array('api_key' => $secret))->getOne('wff_config');
    }
    public function get_config($field = '', $value = '')
    {
        if(!is_string($field) || $field == '' || !is_string($value) || $value == '') {
            return array();
        }

        return $this->mongo_db->where($field, $value)
                            ->getOne('wff_config');
    }
//diallist-----------------
    public function get_dial_list( $dial_list_id = '')
    {
        if( !is_string($dial_list_id) || $dial_list_id == '') {
            return array();
        }

        return $this->mongo_db->where('_id', new MongoId($dial_list_id))
                                ->where('delete', 0)
                                ->getOne('diallist');
    }

    public function get_all_campaign()
    {

        return $this->mongo_db->where('delete', 0)->limit(15000)
                                ->get('diallist');
    }

    public function insert_dial_detail( $dial_detail_data = array())
    {
        if( !is_array($dial_detail_data) || count($dial_detail_data) == 0) {
            return array('status' => 'FAIL');
        }

        $new_dial_detail = $this->mongo_db->insert('diallistDetail', $dial_detail_data);
        
        return array('status' => 'OK', '_id' => $new_dial_detail->{'$id'});
    }

    public function get_remain_list( $dial_list_id = '')
    {
        if(!is_string($dial_list_id) || $dial_list_id == '') {
            return array();
        }

        $result = $this->mongo_db->where("_diallistId", new MongoId($dial_list_id))
                                ->where('assign', '')->limit(15000)
                                ->get('diallistDetail');
        return $result;
    }

    public function get_assigned_list( $dial_list_id = '')
    {
        if( !is_string($dial_list_id) || $dial_list_id == '') {
            return array();
        }

        $result = $this->mongo_db->where("_diallistId", new MongoId($dial_list_id))
                                ->where_ne('assign', '')->limit(15000)
                                ->get('diallistDetail');
        return $result;
    }

    public function get_finished_list( $dial_list_id = '')
    {
        if( !is_string($dial_list_id) || $dial_list_id == '') {
            return array();
        }

        $result = $this->mongo_db->where("diallistId", new MongoId($dial_list_id))
                                ->limit(15000)->get('diallistedDetail');
        return $result;
    }
    
    public function fix_assign_dial_detail( $dial_list_id = '', $assign_list = array())
    {
        if(!is_string($dial_list_id)      || $dial_list_id       == ''
        || !is_array($assign_list)        || count($assign_list) == 0) {
            return false;
        }

        //update dial list
        $dial_list = $this->mongo_db->where('_id', new MongoId($dial_list_id))
                                    ->getOne('diallist');

        $old_agent = explode(',', $dial_list['assignTo']);
        $new_agent = array_keys($assign_list);
        foreach($old_agent as $old_assign)
        {
            if(!empty($old_assign)) {
                $new_agent[] = $old_assign;
            }
        }

        //update dial detail list
        $assign_value = array();
        foreach($assign_list as $assigner => $quantity)
        {
            for($i = 0; $i < $quantity; $i++)
            {
                $assign_value[] = $assigner;
            }
        }
        $assign = count($assign_value);

        foreach($assign_list as $agent_key => $agent_detail)
        {
            if(isset($dial_list['complete_info']) && is_array($dial_list['complete_info'])) {
                $flag = true;
                foreach($dial_list['complete_info'] as &$agent_info)
                {
                    if($agent_info['extension'] == $agent_key) {
                        $agent_info['dial_count'] = $agent_info['dial_count'] + $agent_detail;
                        $flag = false;
                        break;
                    }
                }
                unset($agent_info);

                if($flag) {
                     $dial_list['complete_info'][] = array(
                            'extension'     => (string)$agent_key,
                            'dial_count'    => $agent_detail,
                            'dialled_count' => 0);
                }
            } else {
                $dial_list['complete_info'][] = array(
                        'extension'     => (string)$agent_key,
                        'dial_count'    => $agent_detail,
                        'dialled_count' => 0);
            }
        }

        $update_data = array(
                'assignTo'      => ','.implode(',', array_unique($new_agent)),
                'dial_count'    => $dial_list['dial_count'] + $assign,
                'complete_info' => $dial_list['complete_info']);

        $this->mongo_db->set($update_data)
                        ->where(array('_id' => new MongoId($dial_list_id)))
                        ->update('diallist');
        
        $dial_detail_list = $this->mongo_db->where('_diallistId', new MongoId($dial_list_id))
                                            ->where('assign', '')->limit(15000)
                                            ->get('diallistDetail');

        if(count($dial_detail_list) < $assign) {
             $assign = count($dial_detail_list);
        }

        for($i = 0; $i < $assign; $i++)
        {
            $this->mongo_db->where('_id',  $dial_detail_list[$i]['_id'])
                            ->set(array('assign' => (string)$assign_value[$i]))
                            ->update('diallistDetail');
        }

        $dial_detail_remain_list = $this->mongo_db->where('_diallistId', new MongoId($dial_list_id))
                                                    ->where('assign', '')->limit(15000)
                                                    ->get('diallistDetail');

        return array('affect' => $assign, 'remain' => count($dial_detail_remain_list));
    }
    
    public function ratio_assign_dial_detail( $dial_list_id = '', $assign_list = array())
    {
        if( !is_string($dial_list_id)      || $dial_list_id       == ''
        || !is_array($assign_list)        || count($assign_list) == 0) {
            return false;
        }

        //update dial list
        $dial_list = $this->mongo_db->where('_id', new MongoId($dial_list_id))
                                    ->getOne('diallist');

        $old_agent = explode(',', $dial_list['assignTo']);
        $new_agent = array_keys($assign_list);
        foreach($old_agent as $old_assign)
        {
            if(!empty($old_assign)) {
                $new_agent[] = $old_assign;
            }
        }
        $this->mongo_db->set(array('assignTo' => ','.implode(',', array_unique($new_agent))))
                        ->where(array('_id' => new MongoId($dial_list_id)))
                        ->update('diallist');

        //update dial detail list
        $dial_detail_list = $this->mongo_db->where('_diallistId', new MongoId($dial_list_id))
                                            ->where('assign', '')->limit(15000)
                                            ->get('diallistDetail');

        $mod            = count($dial_detail_list) % array_sum($assign_list);
        $divide         = (count($dial_detail_list) - $mod) / array_sum($assign_list);
        $assign_value   = array();
        foreach($assign_list as $assigner => $ratio)
        {
            for($i = 0; $i < $ratio * $divide; $i++)
            {
                $assign_value[] = $assigner;
            }
        }
        
        for($i = 0; $i < count($assign_value); $i++)
        {
            $this->mongo_db->where('_id',  $dial_detail_list[$i]['_id'])
                            ->set(array('assign' => (string)$assign_value[$i]))
                            ->update('diallistDetail');
        }

        return array('affect' => count($assign_value), 'remain' => $mod);
    }
// END diallist
    public function curl_Get($url,$args = array()){
        $i = 0;
        $arr_line = '';
        if (!empty($args)) {
            foreach ($args as $key => $arg) {
                $arr_line .= ($i==0) ? '?' :'&';
                $arr_line .= $key.'='.$arg;
                $i++;
            }
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => $url.$arr_line,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 100,
          CURLOPT_TIMEOUT => 100,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "GET",
          // CURLOPT_POSTFIELDS => json_encode(array("secret" => $secret)),
//          CURLOPT_HTTPHEADER => array(
//            "accept: application/json",
//            // "authorization: Basic ",
//            "SecretKey: 4N61LL2Hul1gMbtKXk6q7gG2NnYipeUkSR9rq1YJcNPOO6mzgy",
//            "cache-control: no-cache",
//            "content-type: application/json"
//        ),
      ));
        $response = curl_exec($curl);
        $fo = fopen('debugger/responseApi.txt', "w+");
        fwrite($fo, print_r($response, true));
        fclose($fo);
       // print_r($response);
        $responseArr = json_decode($response,true);
        //print_r($responseArr,true);
        if(curl_error($curl)){
            curl_close($curl);
            return array("status"=>-1,"message"=>curl_error($curl));
        }
        if (empty($responseArr)) {
            curl_close($curl);
            return array("status"=>-1,"message"=>"Không thể kết nối");           
        }
        curl_close($curl);
        return $responseArr;
    }

    public function curl_Update($url, $json_array = array()){
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
    //        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => 'requestData='.json_encode($json_array,JSON_UNESCAPED_UNICODE),
    //        CURLOPT_HTTPHEADER => array(
 //               "accept: application/json",
//                // "authorization: Basic ",
//                "SecretKey: 4N61LL2Hul1gMbtKXk6q7gG2NnYipeUkSR9rq1YJcNPOO6mzgy",
  //              "cache-control: no-cache",
    //            "dataType: jsonp",
     //           "Content-type: application/json; charset=utf-8"
         //  ),
        ));
        $response = curl_exec($curl);
        $fo = fopen('debugger/UpdateAPI2.txt', "w+");
        fwrite($fo, print_r(json_encode($json_array,JSON_UNESCAPED_UNICODE), true));
        fclose($fo);
        $fo = fopen('debugger/UpdateAPI.txt', "w+");
        fwrite($fo, print_r($response, true));
        fclose($fo);
       
// print_r($response);
        $responseArr = json_decode($response,true);
        //print_r($responseArr,true);
        if(curl_error($curl)){
            curl_close($curl);
            return array("status"=>-1,"message"=>curl_error($curl));
        }
        if (empty($responseArr)) {
            curl_close($curl);
            return array("status"=>-1,"message"=>"Không thể kết nối");           
        }
        curl_close($curl);
        return $responseArr;
    }

// lấy thông tin khách hàng theo số điện thoại
    public function GetCustomerInfoByPhone($phone_number){
        $args = array(
            'phone'         =>  $phone_number,
            'service_type'  =>  'ALL'
        );
        $url = $this->config->item('url_api_vta').'GetCustomerProfileByPhone';
        return $this->curl_Get($url,$args);
//        $data= array("id"=>00205665,
//            "name"=>"Maria Olala",
//            "person_id"=>"69696969",
//            "passport_id"=>"9696969696",
//            "birthday"=>"01-01-1996",
//            "gender"=>233,
//            "source"=>289,
//            "phone"=>"0969696969",
//            "email"=>"123@gmail.com",
//            "status"=>"Customer",
//            "another_phone"=>"",
//            "service_type"=>"KIM",
//            "note"=>"note 01",
//            "address"=>array(
//                "province_id"=>"01",
//                "district_id"=>"",
//                "ward_id"=>"",
//                "details"=>""
//            ),
//            "related_customer"=>array(
//                array(
//                    "id"=>134555666634,
//                    "name"=>"Maria Ululi",
//                    "related_name"=>"cha",
//                    "person_id"=>"69696969",
//                    "passport_id"=>"9696969696",
//                    "birthday"=>"01-01-1996",
//                    "gender"=>233,
//                    "source"=>289,
//                    "phone"=>"0969696969",
//                    "email"=>"123@gmail.com",
//                    "status"=>"Customer",
//                    "another_phone"=>"",
//                    "service_type"=>"KIM",
//                    "note"=>"note 01",
//                    "address"=>array(
//                        "province_id"=>"01",
//                        "district_id"=>"",
//                        "ward_id"=>"",
//                        "details"=>""
//                    ),
//                ),
//                array(
//                    "id"=>134555666635,
//                    "name"=>"Mikochi",
//                    "related_name"=>"me",
//                    "person_id"=>"69696969",
//                    "passport_id"=>"9696969696",
//                    "birthday"=>"01-01-1996",
//                    "gender"=>233,
//                    "source"=>289,
//                    "phone"=>"0969696969",
//                    "email"=>"123@gmail.com",
//                    "status"=>"Customer",
//                    "another_phone"=>"",
//                    "service_type"=>"KIM",
//                    "note"=>"note 01",
//                    "address"=>array(
//                        "province_id"=>"01",
//                        "district_id"=>"",
//                        "ward_id"=>"",
//                        "details"=>""
//                    ),
//                )
//            )
//        );
//        return array("status"=>1,"message"=>"success","data"=>$data);
    }
    // lấy khách hàng theo id và service id
    public function getCustomerById($id,$service_id) {
//         $data= array("id"=>$id,
//            "name"=>"Maria Olala",
//            "person_id"=>"69696969",
//            "passport_id"=>"9696969696",
//            "birthday"=>"01-01-1996",
//            "gender"=>233,
//            "source"=>289,
//            "phone"=>"0969696969",
//            "email"=>"123@gmail.com",
//            "status"=>"Customer",
//            "another_phone"=>"",
//            "service_type"=>$service_id,
//            "note"=>"note 01",
//            "address"=>array(
//                "province_id"=>"01",
//                "district_id"=>"",
//                "ward_id"=>"",
//                "details"=>""
//            )
//        );
//        return array("status"=>1,"message"=>"success","data"=>$data);
        $args = array(
            'id' => $id,
            'service_type' => $service_id
        );
        $url = $this->config->item('url_api_vta').'GetCustomerProfileByID';
        return $this->curl_Get($url,$args);
    }
 // lấy lịch hẹn theo customer id và service id
    public function getAppointmentByCustomerId($id,$service_id) {
//         $data= array(array("id"=>$id,
//            "branch_id"=>"112",
//            "branch_name"=>"C8 Phạm Hùng, H.Bình Chánh,HCM",
//            "doctor_id"=>"9696969696",
//            "birthday"=>"01-01-1996",
//            "gender"=>233,
//            "source"=>289,
//            "phone"=>"0969696969",
//            "email"=>"123@gmail.com",
//            "status"=>"Customer",
//            "another_phone"=>"",
//            "service_type"=>$service_id,
//            "note"=>"note 01",
//            "address"=>array(
//                "province_id"=>"01",
//                "district_id"=>"",
//                "ward_id"=>"",
//                "details"=>""
//            )
//        ));
//        return array("status"=>1,"message"=>"success","data"=>$data);
         $args = array(
            'id' => $id,
            'service_type' => $service_id,
        );
        $url = $this->config->item('url_api_vta').'GetListScheduleByCustomerID';
        return $this->curl_Get($url,$args);
    }
    // lấy danh sách bác sĩ theo id chi nhánh và service id
    public function getDoctorByBranchId($id,$service_id) {
//         $data= array(array(
//             "service_type"=>$service_id,
//             "id"=>1234,
//             "name"=>"Trần Đăng Khoa"    
//        ),array(
//             "service_type"=>$service_id,
//             "id"=>1235,
//             "name"=>"Trần Tiến"    
//        ),array(
//             "service_type"=>$service_id,
//             "id"=>1236,
//             "name"=>"Sơn Tùng"    
//        ),array(
//             "service_type"=>$service_id,
//             "id"=>1237,
//             "name"=>"Tài Smile"    
//        ));
//        return array("status"=>1,"message"=>"success","data"=>$data);
        $args = array(
            'id' => $id,
            'service_type' => $service_id,
        );
        $url = $this->config->item('url_api_vta').'GetListDoctorByBranch';
        return $this->curl_Get($url,$args);
    }
    //API get danh sách lịch hẹn + trực theo bác sỹ (vào thời điểm CALLCENTER gọi API) 
    public function getAppointmentByDoctorId($doc_id,$from_date,$to_date,$service_id) {
//         $data= array("dataHen"=>array(
//                        array("id"=>0001,
//                            "branch_id"   => 4,
//                            "branch_name" => "396 Đường 3 Tháng 2",
//                            "doctor_id"      =>1237,
//                            "doctor_name" => "Tài Smile",
//                            "starttime" =>1519377360,
//                            "endtime"     =>1519377720,
//                            )),
//                    "dataTruc"=>array(
//                        array("id"=>0002,
//                            "branch_id"   => 4,
//                            "branch_name" => "396 Đường 3 Tháng 2",
//                            "doctor_id"      =>1237,
//                            "doctor_name" => "Tài Smile",
//                            "starttime" =>1519377085,
//                            "endtime"     =>1519377360,
//                            ))
//             );
//        return array("status"=>1,"message"=>"success","data"=>$data,"service_type"=>$service_id);
        $args = array(
            'doc_id' => $doc_id,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'service_type' => $service_id
        );
        $url = $this->config->item('url_api_vta').'GetListScheduleByDoctorID';
        return $this->curl_Get($url,$args);
    }
    //API get danh sách lịch hẹn theo chi nhánh (Khung giờ có thể đặt được lịch hẹn)
    public function getAppointmentByBranchId($branch_id,$from_date,$to_date,$service_id) {
//         $data= array(
//                        array("id"=>0001,
//                            "branch_id"   => 4,
//                            "branch_name" => "396 Đường 3 Tháng 2",
//                            "doctor_id"      =>1237,
//                            "doctor_name" => "Tài Smile",
//                            "starttime" =>1519377360,
//                            "endtime"     =>1519377720,
//                            ),
//                        array("id"=>0002,
//                            "branch_id"   => 4,
//                            "branch_name" => "396 Đường 3 Tháng 2",
//                            "doctor_id"      =>1237,
//                            "doctor_name" => "Tài Smile",
//                            "starttime" =>1519377085,
//                            "endtime"     =>1519377360,
//                            )
//                );
//        return array("status"=>1,"message"=>"success","data"=>$data,"service_type"=>$service_id);
        $args = array(
            'branch_id' => $branch_id,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'service_type' => $service_id
        );
        $url = $this->config->item('url_api_vta').'GetListScheduleByBranch';
        return $this->curl_Get($url,$args);
    }
    //API get danh sách lịch hẹn + lịch trực theo chi nhánh và bác sỹ 
    public function getAppointmentByBranchIdDoctorId($branch_id,$doc_id,$from_date,$to_date,$service_id) {
//          $data= array("dataHen"=>array(
//                        array("id"=>0001,
//                            "branch_id"   => 4,
//                            "branch_name" => "396 Đường 3 Tháng 2",
//                            "doctor_id"      =>1237,
//                            "doctor_name" => "Tài Smile",
//                            "starttime" =>1519377360,
//                            "endtime"     =>1519377720,
//                            )),
//                    "dataTruc"=>array(
//                        array("id"=>0002,
//                            "branch_id"   => 4,
//                            "branch_name" => "396 Đường 3 Tháng 2",
//                            "doctor_id"      =>1237,
//                            "doctor_name" => "Tài Smile",
//                            "starttime" =>1519377085,
//                            "endtime"     =>1519377360,
//                            ))
//             );
//        return array("status"=>1,"message"=>"success","data"=>$data,"service_type"=>$service_id);
         $args = array(
            'branch_id' => $branch_id,
            'doc_id' => $doc_id,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'service_type' => $service_id
        );
        $url = $this->config->item('url_api_vta').'GetListScheduleByBranchAndDoctor';
        return $this->curl_Get($url,$args);
    }
    //API get lịch làm việc nhân viên local sale (Nhân viên Local Sale nào có làm việc trong ngày hôm đó mới trả về)
    public function getLocalSale() {
         $args = array();
        $url = $this->config->item('url_api_vta').'GetListScheduleReceptionBydate';
        return $this->curl_Get($url,$args);
    }
    //Tạo lịch hẹn mới
    public function addAppointment($appointment_data) {
        $url = $this->config->item('url_api_vta').'InsertScheduleCustomer';
        return $this->curl_Update($url, $appointment_data);
    }
    //Cập nhật lịch hẹn
    public function updateAppointment($appointment_data) {
        $url = $this->config->item('url_api_vta').'UpdateScheduleCustomer';
        return $this->curl_Update($url, $appointment_data);
    }
    //API get danh sách chiến dịch
    public function getCampaignList($service_id) {
        $args = array(
            'service_type' => $service_id
        );
        $url = $this->config->item('url_api_vta').'GetListEvent';
        return $this->curl_Get($url,$args);
    }

    //API get danh sách dịch vụ quan tâm
    public function getListServicecare($service_id) {
        $args = array(
            'service_type' => $service_id
        );
        $url = $this->config->item('url_api_vta').'GetListServicecare';
        return $this->curl_Get($url,$args);
    }

    //API get danh sách dịch vụ quan tâm
    public function getListDiscount($service_id, $date_from) {
        $args = array(
            'service_type' => $service_id,
            'date_from'    => $date_from
        );
        $url = $this->config->item('url_api_vta').'GetListDiscount';
        return $this->curl_Get($url,$args);
    }

    //cập nhật danh sách chi nhánh
    public function update_branch($request){
        $this->mongo_db->insert("update_branch_logs",$request);
        return array("status"=>1,"message"=>"success"); 
    }
    //cập nhật lịch hẹn
    public function update_appointment($request){     
        $this->mongo_db->insert("update_appointment_logs",$request);
        if($request['status']==10){
            $appointment_data=$this->mongo_db->where(array("fromcallcenter"=>true,"appointment_id"=>$request["id"],"service_type"=>$request['service_type']))->getOne("appointments");
            if(is_array($appointment_data)&& count($appointment_data)>1){
                $timestamp = strtotime('today midnight');
                $kpi_data=array(
                    "date_create"=>$timestamp,
                    "extension"=>$appointment_data['created_by_id'],
                    "agentname"=>$appointment_data['created_by_name'],
                    "appointment_id"=>$appointment_data['appointment_id'],
                    "service_type"=>$appointment_data['service_type'],
                    "branch_id"=>$appointment_data['branch_id'],
                    "branch_name"=>$appointment_data['branch_name']
                );
                $this->mongo_db->insert("CheckIn_Appointment_KPI",$kpi_data);
            }
        }
        
        return array("status"=>1,"message"=>"success"); 
    }
    //têm mới lịch hẹn
    public function insert_appointment($request){
        $request['fromcallcenter']=false;
        $this->mongo_db->insert("insert_appointment_logs",$request);
        return array("status"=>1,"message"=>"success"); 
    }

    //get danh sách chi nhánh
    public function getListBranch(){
        $args = array();
        $url = $this->config->item('url_api_vta').'GetListBranch';
        return $this->curl_Get($url,$args);
    }
    //têm mới request booking
    public function insert_request_booking($request){
        //$request['fromcallcenter']=false;
        $request['request_time']=time();
        $this->mongo_db->insert("request_booking",$request);
        return array("status"=>1,"message"=>"success"); 
    }

    //Tìm kiếm Customer
    public function searchCustomer($search, $service_type)
    {
        $args = array(
            'search'        => $search,
            'service_type'  => $service_type
        );
        $url = $this->config->item('url_api_vta').'SearchCustomer';
        return $this->curl_Get($url,$args);
    }
}
