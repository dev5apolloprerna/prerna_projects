<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DailyExpenceType extends Model
{
    protected $table = 'daily_expence_type';
    protected $primaryKey = 'expence_type_id';
    public $timestamps = true;
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = ['type', 'slug', 'iStatus', 'isDelete']; // ← add slug

    protected $casts = [
        'iStatus'  => 'integer',
        'isDelete' => 'integer',
    ];

    /** Scope: only not-deleted rows */
    public function scopeAlive($q)
    {
        return $q->where('isDelete', 0);
    }

    protected static function booted()
    {
        static::creating(function (self $m) {
            if (empty($m->slug)) {
                $m->slug = static::makeUniqueSlug($m->type);
            }
        });

        static::updating(function (self $m) {
            if ($m->isDirty('type')) {
                $m->slug = static::makeUniqueSlug($m->type, $m->getKey());
            }
        });
    }

    public static function makeUniqueSlug(string $type, ?int $ignoreId = null): string
    {
        $base = Str::slug($type) ?: 'type';
        $slug = $base;
        $i = 2;

        while (
            static::query()
                ->where('slug', $slug)
                ->where('isDelete', 0)
                ->when($ignoreId, fn($q) => $q->where('expence_type_id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
