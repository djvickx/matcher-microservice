<?php

namespace App\Models;

class Property{

    private string $name;
    private string $address;
    private string $propertyType;
    private array $fields;
    
    public function getPropertyType(){
        return $this->propertyType;
    }
    public function getPropertyFields(){
        return $this->fields;
    }
    
}