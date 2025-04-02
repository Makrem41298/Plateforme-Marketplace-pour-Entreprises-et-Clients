<?php

namespace App\Http\Controllers;

trait apiResponse
{
    public function apiResponse($message=null,$data=null,$status=null){
        return response()->json([
                'message' => $message,
                'data' =>$data ]
            ,$status);
    }

}
