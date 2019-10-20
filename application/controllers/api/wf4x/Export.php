<?php
/**
* Excel dengan CI & Spout
*
*/
//load Spout Library
require_once APPPATH.'/third_party/spout/src/Spout/Autoloader/autoload.php';

//lets Use the Spout Namespaces
// use Box\Spout\Reader\Common\Creator\ReaderFactory;
// use Box\Spout\Common\Type;
               use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class Export extends CI_Controller {

      public function readExcelFile() {

          try {

               //Lokasi file excel
               $file_path = "/var/www/html/worldfone4xs_ibm/upload/users/import/Data_thu_vien_chung59.xlsx";

               $reader = ReaderEntityFactory::createXLSXReader();
               $reader->open($file_path); //open the file

                $i = 0;

                /**
                * Sheets Iterator. Kali aja multiple sheets
                **/
                $rowData = array();
                foreach ($reader->getSheetIterator() as $sheet) {

                    //Rows iterator
                    foreach ($sheet->getRowIterator() as $row) {
                        // if($i == 0){
                        //    continue;
                        // }

                        $data = $row->toArray();

                           ++$i;
                           // if ($i == 10) {
                           //    exit;
                           // }
                        array_push($rowData, $data);

                     }
                }
                echo "<pre>";
                print_r($rowData);
                echo "Total Rows : " . $i;
                $reader->close();


               echo "Peak memory:", (memory_get_peak_usage(true) / 1024 / 1024), " MB";

      } catch (Exception $e) {

              echo $e->getMessage();
              exit;
      }

  }//end of function


}//end of class