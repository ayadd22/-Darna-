<?php

namespace App\Cache;


final class PropertyCacheKeys
{
   

   
    public const TTL_GOVERNORATE_STATS = 600;

    
    public const TTL_FEATURED = 900;

   
    public const TTL_DETAIL = 1800;



  
    public static function detail(int $propertyId): string
    {
        return "properties:detail:{$propertyId}";
    }

 
    public static function featured(): string
    {
        return 'properties:featured';
    }

  
    public static function governorateStats(): string
    {
        return 'properties:stats:by-governorate';
    }
}
