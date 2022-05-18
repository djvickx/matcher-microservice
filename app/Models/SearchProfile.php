<?php

namespace App\Models;

class SearchProfile{

    private string $name;
    private string $propertyType;
    private array $searchFields;

    public function getSearchProfilePropertyType(){
        return $this->propertyType;
    }
    public function getSearchProfileFields(){
        return $this->searchFields;
    }

}