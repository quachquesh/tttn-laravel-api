<?php
namespace App\Http\Controllers;

use Lcobucci\JWT\Configuration;
use Laravel\Passport\Token;
use App\Models\Lecturer;
use App\Models\Student;

class Helpers {
    public function __construct() {}

    public function getUserData($request)
    {
        $bearerToken = $request->bearerToken();
        if ($bearerToken) {
            $tokenId = Configuration::forUnsecuredSigner()->parser()->parse($bearerToken)->claims()->get('jti');
            $token = Token::find($tokenId);
            if (!$token->revoked) {
                $table = strtolower($token->name);
                $userId = $token->user_id;
                $data = null;
                if ($table == "students") {
                    $data = Student::find($userId);
                } else {
                    $data = Lecturer::find($userId);
                }
                return $data;
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function getToken($request)
    {
        $bearerToken = $request->bearerToken();
        if ($bearerToken) {
            $tokenId = Configuration::forUnsecuredSigner()->parser()->parse($bearerToken)->claims()->get('jti');
            $token = Token::find($tokenId);
            return $token;
        } else {
            return null;
        }
    }

    public function object2Array($obj)
    {
        $result = array();
        foreach ($obj as $value) {
            array_push($result, $value);
        }
        return $result;
    }
}
