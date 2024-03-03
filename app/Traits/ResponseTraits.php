<?php

namespace App\Traits;

trait ResponseTraits{

  public function response(string $message, bool $status, int $code, mixed $data = null)
  {
    return response()->json([
      'message' => $message,
      'status' => $status,
      'data' => $data
    ], $code);
  }

}