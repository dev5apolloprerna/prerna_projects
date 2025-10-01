<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Tanker extends Model
{
    protected $table = 'tanker_master';
    protected $primaryKey = 'tanker_id';

    protected $fillable = [
        'tanker_name',
        'godown_id',
        'tanker_code',
        'slug',       // ← add slug
        'status',
        'iStatus',
        'isDelete',
    ];

    public function godown()
    {
        return $this->hasOne( GodownMaster::class, 'godown_id', 'godown_id')
                    ->where('isDelete', operator: 0);
    }
    public function order()
    {
        return $this->hasOne( OrderMaster::class, 'tanker_id', 'tanker_id')
                    ->where('isDelete', operator: 0);
    }
    protected static function booted()
    {
        // On create: set slug if empty
        static::creating(function (Tanker $model) {
            if (empty($model->slug)) {
                $model->slug = static::makeUniqueSlug($model->tanker_name);
            }
        });

        // On update: if name changed, refresh slug
        static::updating(function (Tanker $model) {
            if ($model->isDirty('tanker_name')) {
                $model->slug = static::makeUniqueSlug($model->tanker_name, $model->getKey());
            }
        });
    }

    public static function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'tanker';
        }

        $slug = $base;
        $i = 2;

        while (
            static::query()
                ->where('slug', $slug)
                ->where('isDelete', 0)
                ->when($ignoreId, fn ($q) => $q->where('tanker_id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }
}
