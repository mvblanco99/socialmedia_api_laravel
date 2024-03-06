<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait CacheTraits{
  


  //Extraer informacion de cache
  public function verifiedKey(string $key):bool
  {
    return Cache::has($key);
  }

  public function get(string $key):mixed
  {
    return Cache::get($key);
  }

  //Guardar informacion en cache
  public function createInfoCache(string $key, $value, $ttl=8000)
  {
    $isCreated = Cache::add($key, $value, $ttl);
    return $isCreated;
  }

  //Actualizar informacion de cache
  public function update($key,$value)
  {
    $isUpdated = Cache::put($key,$value);
    return $isUpdated;
  }

  //Destruir informacion de cache
  public function destroy($key){
    $isDeleted = Cache::forget($key);
    Cache::flush();
    return $isDeleted;
  }
}