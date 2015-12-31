<?php
   /**
    * @author Vasinsky Igor
    * @email igor.vasinsky@gmail.com
    * @copyright 2013
    *  
    * Class files upload
    * Features: 
    *  -check the validity of the file types 
    *  -check the file size 
    *  -loading files 
    *  -modify the file name
    */    
    /**  Example of use
     * ---------------------------------------------------------------------------
      if(isset($_POST['send'])){
          //Permitted file types
          $validTypes = array('image/jpg','image/jpeg','image/gif','image/bmp'); 
          Upload::validType($validTypes);
          
          //name of input type="file"
          Upload::$index = 'images';
          //Maximum upload size
          Upload::$size = 15000;
          
          //Validation of file types  
          $files = Upload::validate();
          
          //Uploading files to a specified directory
          $result = Upload::uploadFiles($files, 'tmp',1);
          
          echo '<pre>' . print_r($result, 1) . '</pre>'; 
      }      
      
      $ result - two-dimensional array with elementamimi 'valid' (downloaded) and error ('not downloaded')
    * ---------------------------------------------------------------------------
    */

   class Upload{
        /**
         * name of input type="file"
         */ 
        static $index = 'images';
        /**
         * the maximum file size
         */ 
        static $size = 600;
        /**
         * Internal variable storage dopustipyh mime types
         */ 
        static $validType = array();
        
        public function __construct(){
            
        }
        /**
         * Setting mime types of files
         * http://www.spravkaweb.ru/php/pril/mime 
         * @param array  
         *  array('mime/type1', 'mime/type1')
         *  empty array - no restrictions
         */ 
        static public function validType($type){
            self::$validType = $type;
        }
        
        /**
         * Retrieving Data downloadable files
         * @return array
         */  
        static public function getFiles(){
             if(empty($_FILES)){
                
               return false;
             }
             else{
                foreach($_FILES[self::$index]['name'] as $key=>$name){
                    $pathinfo = pathinfo($name);
                    
                    
                    
                    if($pathinfo['basename'] !=''){
                        $filename = $pathinfo['filename'];
                        $ext = $pathinfo['extension'];
                  
                        $hashname = sha1($_FILES[self::$index]['tmp_name'][$key].$pathinfo['basename'].microtime());
                        
                        $errors = array(
                            0=>'',
                            1=>'When the file size exceeded the maximum size that is specified 
                                upload_max_filesize directive configuration file',
                            2=>'Upload file size exceeded the value MAX_FILE_SIZE, specified in the form of HTML',
                            3=>'Uploaded file was only partially',
                            4=>'No file was uploaded',
                            6=>'Missing a temporary folder',
                            7=>'Failed to write file to disk',
                            8=>'PHP-stop downloading the file extension. PHP does not provide a way 
                                determine what extension stop file upload'
                        );
                        
                        $filesdata[] = array(
                                'name'=>$filename,
                                'ext'=>isset($ext) ? $ext : '-',
                                'type'=>$_FILES[self::$index]['type'][$key],
                                'hashname'=>$hashname,
                                'tmpname'=>isset($_FILES[self::$index]['tmp_name'][$key])
                                           ? $_FILES[self::$index]['tmp_name'][$key]
                                           : '-',
                                'error'=>$errors[$_FILES[self::$index]['error'][$key]],
                                'size'=>ceil($_FILES[self::$index]['size'][$key]/1024)
                        );
                    }
                }
                return isset($filesdata) ? $filesdata : false;
             } 
        }
        
        /**
         * Checking validity of uploaded files
         * @return array/bool
         */ 
        public static function validate(){
            if(self::getFiles() === false){
                return false;
            }
            else{
                if(empty(self::$validType)){
                    return self::getFiles();
                }
                else{
                    
                    foreach(self::getFiles() as $k=>$v){
                       if(!empty($v['error'])){
                           if($returnOnlyValidFiles = 1)
                               $files[] = $v; 
                       } 
                       elseif(!in_array($v['type'], self::$validType)){
                           $v['error'] = 'It is not permissible to download a file type: '.$v['type'];
                           $files[] = $v;
                       }
                       elseif($v['size']>self::$size){
                           $v['error'] = 'Unacceptable load a file type '.self::$size .' kb';
                           $files[] = $v;                
                       }
                       else{
                           $files[] = $v;
                       }
                    } 
                    return empty($files) ? false : $files;                 
                }
            }
        }
        
        /**
         * The file upload 
         * @ Param array - then returned Upload :: validate () 
         * @ Param string directory download 
         * @ Param bool 
         * False - use the original file names 
         * True - use the hash of the file name getFiles () element hashname 
         * Use only after stage Upload :: validate ())
         * 
         * @return array/bool
         */ 
        public static function uploadFiles($validate_files, $dir, $rename=false, $prefix=false){

            if(!is_array($validate_files)){
                return false;
            }
            
            if($prefix !== false){
                $validate_files = self::setPrefix($validate_files, $prefix);
            }
            
            
            
            $files['valid'] = array();
            $files['error'] = array();
                
            foreach($validate_files as $k=>$file){
                
                $name = ($rename === false) ? $file['name'] : $file['hashname'];

                if($file['error'] == ''){
                     
                    $file['uploaddir'] = $dir;
                    
                    if(move_uploaded_file($file['tmpname'], $dir.'/'.$name.'.'.$file['ext'])){
                        $file['fullpath'] = $dir.'/'.$name.'.'.$file['ext'];
                        $files['valid'][] = $file;
                    }    
                    else{
                        $file['error'] = '&#1053;&#1077; &#1087;&#1086;&#1083;&#1091;&#1095;&#1080;&#1083;&#1086;&#1089;&#1100; &#1089;&#1082;&#1086;&#1087;&#1080;&#1088;&#1086;&#1074;&#1072;&#1090;&#1100; &#1092;&#1072;&#1081;&#1083;';
                        $files['error'][] = $file;
                    }
                } 
                else{
                    $files['error'][] = $file;
                }
            }
            
            return isset($files) ? $files : false;
        }
        
        /**
         *  Method of modifying the file name - add a prefix
         *  @param array
         *  @param string
         *  return array
         */ 
         public function setPrefix($files, $prefix){
              foreach($files as $k=>$f){
                  $mod_files[] = array(
                                     'name'=>$prefix.'_'.$f['name'],
                                     'ext'=>$f['ext'],
                                     'type'=>$f['type'],
                                     'hashname'=>$f['hashname'],
                                     'tmpname'=>$f['tmpname'],
                                     'error'=>$f['error'],
                                     'size'=>$f['size'],
                                        
                                     );
              }
            
             return $mod_files;
         }
        
        /**
         * Deleting a file from a directory
         * @param string
         * @return bool
         */ 
        static public function deleteFile($pathtofile){
            return (!unlink($pathtofile)) ? false : true;
        }
        
        static public function move_file($pathtofile, $dir, $del = true){
            $pathinfo = pathinfo($pathtofile);
            
            $result = copy($pathtofile, $dir.'/'.$pathinfo['basename']);

            if($del === true)
                Upload::deleteFile($pathtofile);
            
            return $result;
        }
        
        
   }
?>
