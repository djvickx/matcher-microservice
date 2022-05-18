<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Models\SearchProfile;

use Illuminate\Http\Request;

class MatchController extends Controller
{
    //
    public function show($propertyId)
    {
        $properties = [];
        $searchProfiles = [];
        //Populate dummy data to test endpoint
        [$properties, $searchProfiles] = $this->seedData($properties, $searchProfiles);
        $givenProperty = array_filter($properties, function ($property) use ($propertyId) {
            return $property['propertyType'] == $propertyId;
        });

        $matchedProfiles = [];
        //If a property is found for given property id
        if (count($givenProperty) > 0) {
            $givenProperty = $givenProperty[0];
            $propertyFields = $givenProperty['fields'];
            $selectedProfiles = array_filter($searchProfiles, function ($profile) use ($propertyId) {
                return $profile['propertyType'] == $propertyId;
            });
            //Check if a search profile is found with the same property id
            if (count($selectedProfiles) > 0) {
                foreach ($selectedProfiles as $profile) {
                    $matchedProfile = [];
                    $matchedProfile['score'] = 0;
                    $matchedProfile['strictMatchesCount'] = 0;
                    $matchedProfile['looseMatchesCount'] = 0;
                    //Find out matching fields in search profiles and property having same property type
                    $matchingFields = array_intersect_key($profile['searchFields'], $propertyFields);
                    if (count($matchingFields) > 0) {
                        $matchedProfile['id'] = $profile['id'];
                        //Iterate through the matching fields to find out matches
                        foreach ($matchingFields as $fieldKey => $fieldValue) {
                            if (gettype($fieldValue) == 'string') {
                                //If type is string and value is same its a strict match
                                if ($fieldValue == $propertyFields[$fieldKey]) {
                                    $matchedProfile['strictMatchesCount'] += 1;
                                }
                            }
                            if (gettype($fieldValue) == 'array') {
                                if ($fieldValue[0] == null) {
                                    $fieldValue[0] = 0;
                                }
                                if ($fieldValue[1] == null) {
                                    $fieldValue[1] = PHP_INT_MAX;
                                }
                                //If type is array and value of property field is in given range
                                if ($propertyFields[$fieldKey] >= $fieldValue[0] && $propertyFields[$fieldKey] <= $fieldValue[1]) {
                                    $matchedProfile['strictMatchesCount'] += 1;
                                } else {
                                    //If the value is outside of range
                                    $fieldValue[0] = $fieldValue[0] - round(0.25 * $fieldValue[0]);
                                    $fieldValue[1] = $fieldValue[1] + round(0.25 * $fieldValue[1]);
                                    if ($propertyFields[$fieldKey] >= $fieldValue[0] && $propertyFields[$fieldKey] <= $fieldValue[1]) {
                                        $matchedProfile['looseMatchesCount'] += 1;
                                    }
                                }
                            }
                        }
                        $matchedProfile['score'] = $matchedProfile['strictMatchesCount'] + $matchedProfile['looseMatchesCount'];
                        array_push($matchedProfiles, $matchedProfile);
                    }
                }
                if (count($matchedProfiles) > 0) {
                    usort($matchedProfiles, function ($a, $b) {
                        $a = $a['score'];
                        $b = $b['score'];
                        if ($a == $b) return 0;
                        return ($a > $b) ? -1 : 1;
                    });
                }
            }
        }

        return response()->json([
            'data' => $matchedProfiles
        ]);
    }

    public function seedData($properties, $searchProfiles)
    {
        $property = [
            "name" => "Awesome house in the middle of my town",
            "address" => "Main street 17, 12456 Berlin",
            "propertyType" => "d44d0090-a2b5-47f7-80bb-d6e6f85fca90",
            "fields" => [
                "area" => "180",
                "yearOfConstruction" => "2010",
                "rooms" => "5",
                "heatingType" => "gas",
                "parking" => true,
                "returnActual" => "12.8",
                "price" => "1500000"
            ]
        ];
        array_push($properties, $property);

        $searchProfile = [
            "id" => "1",
            "name" => "Looking for any Awesome realestate!",
            "propertyType" => "d44d0090-a2b5-47f7-80bb-d6e6f85fca90",
            "searchFields" => [
                "price" => ["0", "2000000"],
                "area" => ["150", "250"],
                "yearOfConstruction" => ["2010", "2020"],
                "rooms" => ["7", null]
            ],
            "returnActual" => ["15", "12"],
        ];
        array_push($searchProfiles, $searchProfile);

        $searchProfile =  [
            "id" => "2",
            "name" => "This is an Awesome realestate!",
            "propertyType" => "d44d0090-a2b5-47f7-80bb-d6e6f85fca90",
            "searchFields" => [
                "price" => ["0", "1400000"],
                "area" => ["150", "20"],
                "yearOfConstruction" => ["2010", "2020"],
                "rooms" => ["8", null]
            ],
            "returnActual" => ["15", "12"],
        ];
        array_push($searchProfiles, $searchProfile);

        return array($properties, $searchProfiles);
    }
}
