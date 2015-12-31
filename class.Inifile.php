<?php
 /**
   * Class for reading and writing ini file
   * For more info please visit to http://phpforum.ru/index.php?showtopic=81659
   */

   /*
    Create an empty file
    create ()

    reading a file
    read ()

    Adding section
    addSection ()

    Adding a Parameter to section
    addParam ()

    Save a file
    save ()

    Removing section
    deleteSection ()

    Removing the parameter section
    deleteParam ()

    Change the name of the section
    changeSectionName ()

    Change the name of the parameter section
    changeParamName ()

    Change the parameter section
    changeParamValue ()

    Preview of changes - allows us to observe changes without saving the file
    preview ()

    */

    /*
        Work with the class
        ----------------------------------------------------------------------
        Create a new Ini file
        $ ini = new iniFile ('data.ini');


        Adding section
        $ ini-> addSection ('general');


        Adding Parameters section
        $ ini-> addParam ('general', 'param1', 'value1');
        $ ini-> addParam ('general', 'param2', 'value2');


        Saving a file ( only when you add a section or parameter )
        $ ini-> save ();


        Reading from a file
        $ file = $ ini-> read ();


        After this kind of file is parsed array
        Array
        (
            [general] => Array
                (
                    [param1] => value1
                    [param2] => value2
                )

        )


        and comprises
        [general]
        param1 = value1
        param2 = value2


        To change the name of the section without saving ( for predosmotra )
           $ ini-> changeSectionName ('general', 'new_general_config', false);
           $ preview = $ ini-> preview ();

        To immediately change and save
        $ ini-> changeSectionName ('general', 'new_general_config');


        result
        Array
        (
            [new_general_config] => Array
                (
                    [param1] => value1
                    [param2] => value2
                )

        )


        To change the name of the parameter certain section without saving - with Preview
           $ ini-> changeParamName ('general', 'param1', 'new_name_param1', false);
           $ preview = $ ini-> preview ();

        result
        Array
        (
            [general] => Array
                (
                    [new_name_param1] => value1
                    [param2] => value2
                )

        )


        Edit and save the name of the parameter immediately
        $ ini-> changeParamName ('general', 'param1', 'new_name_param1');


        Change parameter value a particular section ( without saving)
        ini-> changeParamValue ('general', 'param1', 'new_value', false);

        Change and immediately save
        ini-> changeParamValue ('general', 'param1', 'new_value');

        Removing section
        $ ini-> deleteSection ('general');

        Deleting it from the section
        $ ini-> deleteParam ('general', 'param1');
    */
   class iniFile{
      /**
       * (string)Path and name of the ini file
       */
      public $ini_file;
      /**
       * (string) All ini file as a string (file_get_contents ())
       */
      public $ini_data;
      /**
       *  (array) Ini file contents after work parse_ini_file (file, true)
       */
      public $ini_array = array();

      /**
       * To preview ini array (before saving)
       */
       private $preview;

      const WRONG_EXT = 'The file extension must be *. Ini';
      const NOT_FOUND = 'The specified file is not found';
      const WRONG_READ = 'Unable to read the specified file';
      const ERROR_SAVE = 'I can not save a file';
      /**
       * @param string Path / filename
       * @return void
       */
      public function __construct($path_to_ini_file){
         $this->ini_file = $path_to_ini_file;

         if(!preg_match("#\.ini$#", $this->ini_file)){
             $this->Exept(self::WRONG_EXT);
         }
         else{
            if(file_exists($this->ini_file)){
                $this->ini_array = parse_ini_file($this->ini_file,true);
                $this->ini_data = file_get_contents($this->ini_file);
            }
         }
      }

      /**
       * Method throws an exception in case of errors
       * @param string - exception text
       */
      private function Exept($text){
          throw new Exception($text);
      }

      /**
       * Method to create an empty ini file
       * @param string Path / filename
       * @return bool
       */
      public function create(){
          return file_put_contents($this->ini_file,'');
      }
      /**
       * Method returns the contents of the ini file as a string
       * @return string
       */
      public function readStringIni(){
          if(!file_exists($this->ini_file))
              $this->Exept(self::NOT_FOUND);
          elseif(!is_readable($this->ini_file))
              $this->Exept(self::WRONG_READ);
          else{
             return $this->ini_data;
          }
      }
      /**
       * The method returns the parsed ini file
       * @return array
       */
      public function read(){
          if(!file_exists($this->ini_file))
              $this->Exept(self::NOT_FOUND);
          elseif(!is_readable($this->ini_file))
              $this->Exept(self::WRONG_READ);
          else{
             return $this->ini_array;
          }
      }

      /**
       *  The method adds a section to your
       *  @param string - name of section
       *  @return void
       */
       public function addSection($namesection){
            file_put_contents($this->ini_file, '['.$namesection.']'.PHP_EOL);
       }

      /**
       * This method adds the section
       * @ Param string - the name of the section in which the parameter is added
       * @ Param string - name of the parameter
       * @ Param mixid - value
       *  @return void
       */
       public function addParam($namesection, $nameparam, $value){
            $this->ini_array[$namesection][$nameparam]= $value;
       }


       /**
        * This method removes the entire section with parameters
        * @ Param string section naimanovanie
        * @ Param bool
        * True - delete
        * False - only remove from the array - the possibility predosmotra method preview ()
        * @ Return void
        */
        public function deleteSection($namesection, $drop=true){

             if(is_array($this->ini_array)){
                foreach($this->ini_array as $gen=>$param){
                    if($gen != $namesection){
                        foreach($param as $p=>$v){
                             $change[$gen][$p] = $v;
                        }
                    }
                }

                $this->preview = $change;
             }

             if($drop === true){
                 $this->ini_array = $this->preview;
                 $this->save();
             }
        }

       /**
        * This method removes the entire section with parameters
        * @ Param string section naimanovanie
        * @ Param string name of the parameter
        * @ Param bool
        * True - delete
        * False - only remove from the array - the possibility predosmotra method preview ()
        * @ Return void
        */
        public function deleteParam($namesection, $paramname, $drop=true){
             if(is_array($this->ini_array)){
                foreach($this->ini_array as $gen=>$param){

                    if($gen == $namesection){
                        foreach($param as $p=>$v){
                            if($p != $paramname)
                                 $change[$gen][$p] = $v;
                        }
                    }
                }

                $this->preview = $change;
             }

             if($drop === true){
                 $this->ini_array = $this->preview;
                 $this->save();
             }
        }

        /**
         * Method to change the section name
         * @ Param string the old name
         * @ Param string new name
         * @ Param bool
         * True - delete
         * False - only remove from the array - the possibility predosmotra method preview ()
         *  @return void
         */
         public function changeSectionName($oldname, $newname,$change=true){
             if(is_array($this->ini_array)){
                foreach($this->ini_array as $gen=>$param){
                    if($gen == $oldname)
                        $gen = $newname;

                    foreach($param as $p=>$v){
                         $change[$gen][$p] = $v;
                    }

                }

                $this->preview = $change;
             }

             if($change === true){
                 $this->ini_array = $this->preview;
                 $this->save();
             }
         }

        /**
         * Method to change the name of the parameter section
         * @ Param string name of the section
         * @ Param string the old name
         * @ Param string new name
         * @ Param bool
         * True - delete
         * False - only remove from the array - the possibility predosmotra method preview ()
         *
         *  @return void
         */
         public function changeParamName($namesection, $oldname, $newname,$change=true){

             if(is_array($this->ini_array)){
                foreach($this->ini_array as $gen=>$param){
                    if($gen == $namesection){
                        foreach($param as $p=>$v){
                             if($p == $oldname)
                                 $p = $newname;

                             $change[$gen][$p] = $v;
                        }
                    }
                }

                $this->preview = $change;
             }

             if($change === true){
                 $this->ini_array = $this->preview;
                 $this->save();
             }
         }

        /**
         * Method to change the parameter values specific section
				 * @ Param string name of the section
         * @ Param string name of the parameter
				 * @ Param string the new value
			   * @ Param bool
 				 * True - delete
			   * False - only remove from the array - the possibility predosmotra method preview ()
         * @return void
         */
        public function changeParamValue($namesection, $nameparam, $newvalue,$set=true){
             if(is_array($this->ini_array)){
                foreach($this->ini_array as $gen=>$param){
                    if($gen == $namesection){
                        foreach($param as $p=>$v){
                             if($p == $nameparam)
                                 $v = $newvalue;

                             $change[$gen][$p] = $v;
                        }
                    }
                }

                $this->preview = $change;
             }

             if($set === true){
                 $this->ini_array = $this->preview;
                 $this->save();
             }
        }


        /**
         * Method for monitoring changes array ini file during editing using the class
         * @return array
         */
        public function preview(){
                return $this->preview;
        }

       /**
        * Method saves added sections and parameters in the file
        * @return bool
        */
        public function save(){

            if(is_array($this->ini_array)){
                $string =  '';
                foreach($this->ini_array as $gen=>$param){
                     $string .= '['.$gen.']'.PHP_EOL;

                     foreach($param as $p=>$v){
                        $string .= $p.'='.$v.PHP_EOL;
                     }
                }
                if(!file_put_contents($this->ini_file,$string)){
                   $this->Exept(self::ERROR_SAVE);
                   return false;
                }

            }

            return true;
        }
   }
?>
