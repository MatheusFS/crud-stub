<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Model2 extends Model {


    public function getColumnsType(){

        $fields = \DB::select(\DB::raw('SHOW COLUMNS FROM '.$this->table));
        $types = [];
        foreach ($fields as $i => $info) {
            $type_size = explode('(',$info->Type);
            if(count($type_size)>1) $type_size[1] = explode(',', preg_replace('/[\(\)\']/', '', $type_size[1]));
            $types[$info->Field] = $type_size;
        }
        return $types;
    }

    public function getEditHTML($classes = 'mb-1'){

        $html = '';

        foreach ($this->editable as $column => $as){

            $this->attributes[$column] = isset($this->attributes[$column]) ? $this->attributes[$column] : ''; 

            switch($this->getColumnsType()[$column][0]){

                case 'varchar':

                    if(str_contains($column, 'color')){

                        $html .= "<div class='input-group $classes'>";
                            $html .= "<div class='input-group-prepend'>";
                                $html .= "<div class='input-group-text p-1'>";
                                    $html .= "<input type='color' id='$column' placeholder='$as' value='".$this->attributes[$column]."' class='border-0 p-0' onchange=\"document.querySelector('.modal.show [name=\'".\Str::singular($this->table)."[$column]\']').value = this.value;\">";
                                $html .= "</div>";
                            $html .= "</div>";
                            $html .= "<input type='text' name='".\Str::singular($this->table)."[$column]' placeholder='$as' value='".$this->attributes[$column]."' class='form-control' onkeyup=\"document.querySelector('.modal.show #$column').value = this.value;\">";
                        $html .= "</div>";
                    }else{
                        $html .= "<input type='text' name='".\Str::singular($this->table)."[$column]' placeholder='$as' value='".$this->attributes[$column]."' class='form-control $classes'>";
                    }
                break;
        
                case 'enum':
                case 'set':
                    $html .= "<select name='".\Str::singular($this->table)."[$column]' class='form-control $classes'>";
                        foreach ($this->getColumnsType()[$column][1] as $option){

                            $html .= "<option ".($this->attributes[$column] == $option ? 'selected' : '')." value='$option'>".ucfirst($option).'</option>';
                        }
                    $html .= '</select>';
                break;
        
                case 'int':
                    $html .= "<input type='number' name='".\Str::singular($this->table)."[$column]' placeholder='$as' value='".$this->attributes[$column]."' class='form-control $classes'>";
                break;
        
                default:
                    $html .= "<input type='text' name='".\Str::singular($this->table)."[$column]' placeholder='$as' value='".$this->attributes[$column]."' class='form-control $classes'>";
            }
        }

        return $html;
    }

}