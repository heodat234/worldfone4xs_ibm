<?php 
//thanh le. 7-11-18
    class  shipping_model extends CI_Model{
       public  function __construct(){
           $this->load->library('mongo_db');
       }
       
        public function getProvince(){ 
       
            $json = $this->mongo_db->get('Province');
        
            return $json;
        }
        
        public function getDistricts($id){ 
           
            
                $json = $this->mongo_db->where('ProvinceID', (int) $id)->get('Districts');
            
                   return $json;  
        }
        public function getWards($id){ 
          
                $json = $this->mongo_db->where('DistrictID', (int) $id)->get('Wards');
            
                  return $json;  
        }
        //create order ghn
        public function create_Order($url,$data){
            $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => $data,
              CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Postman-Token: fd12e5cd-97bc-4296-862f-56f2d51d01aa",
                "cache-control: no-cache"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
              echo "cURL Error #:" . $err;
            } else {
              return $response;
            }
            
        }
         public function FindAvailableServices($url,$data){
           $curl = curl_init();

            curl_setopt_array($curl, array(
              CURLOPT_URL => $url,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_POSTFIELDS => json_encode($data),
              CURLOPT_HTTPHEADER => array(
                "Content-Type: application/json",
                "Postman-Token: 8fc725b8-3d2d-4c38-9f59-80cc90cd662f",
                "cache-control: no-cache"
              ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
              echo "cURL Error #:" . $err;
            } else {
              return $response;
            }
            
        }
        
        public function CURL($url,$data){ 
        
        $curl = curl_init();

         curl_setopt_array($curl, array(
           CURLOPT_URL => $url,
           CURLOPT_RETURNTRANSFER => true,
           CURLOPT_ENCODING => "",
           CURLOPT_MAXREDIRS => 10,
           CURLOPT_TIMEOUT => 30,
           CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
           CURLOPT_CUSTOMREQUEST => "POST",
           CURLOPT_POSTFIELDS => json_encode($data),
           CURLOPT_HTTPHEADER => array(
             "Content-Type: application/json",
             "cache-control: no-cache"
           ),
         ));

         $response = curl_exec($curl);
         $err = curl_error($curl);

         curl_close($curl);

         if ($err) {
           echo "cURL Error #:" . $err;
         } else {
           return $response;
         }
     } 
    }

?>