<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Storage;

class BannerRepository extends Repository
{
    public static function getAll(bool $with_all = false)
    {
        $now = now();
        $eloquent = static::getModel()::orderBy('sort', 'desc');

        if ($with_all === false) {
            $eloquent->where(function ($query) use ($now) {
                $query->where('started_at', '<=', $now)
                    ->where('ended_at', '>=', $now);
            })->orWhere(function ($query) use ($now) {
                $query->where('started_at', '<=', $now)
                    ->whereNull('ended_at');
            });
        }

        return $eloquent->get();
    }
    
    public static function create(array $data)
    {
        $model = static::getModel();
        $banner = new $model();
        
        $new_banner_path = 'banner/'.$data['path']->getClientOriginalName();
        Storage::disk('public')->put($new_banner_path, file_get_contents($data['path']->getRealPath()));
        $banner->path = $new_banner_path;
              
        if (isset($data['target_url'])) {
            $banner->target_url = $data['target_url'];
        }
        if (isset($data['started_at'])) {
            $banner->started_at = $data['started_at'];
        }
        if (isset($data['ended_at'])) {
            $banner->ended_at = $data['ended_at'];
        }
        
        $banner->save();
        return $banner;
    }

    public static function getModel()
    {
        return \App\Models\Banner::class;
    }
}
