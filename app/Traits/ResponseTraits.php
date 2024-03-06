<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ResponseTraits{

  public function response(string $message, bool $status, int $code, mixed $data = null):JsonResponse
  {
    return response()->json([
      'message' => $message,
      'status' => $status,
      'data' => $data
    ], $code);
  }

  public function unauthorizedResponse():JsonResponse
  {
    return response()->json([
      'status' => false,
      'message' => 'Unauthorized'
    ],403);
  }

  public function serviceUnavailableResponse($message = ""):JsonResponse
  {
    return response()->json([
      'status' => false,
      'message' => 'Service Unavailable ' . $message
    ],503);
  }

}