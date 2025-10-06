<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GodownMaster extends Model
{
    protected $table      = 'godown_master';
    protected $primaryKey = 'godown_id';
    public $timestamps    = true;

    protected $fillable = [
        'godown_address',
        'Name',       // capital N as per schema
        'slug',
        'iStatus',
    ];

    // Hide soft-deleted rows
    public function scopeNotDeleted($q)
    {
        return $q->where('isDelete', 0);
    }

    protected static function booted()
    {
        // Set slug on create if missing
        static::creating(function (GodownMaster $m) {
            if (empty($m->slug)) {
                $m->slug = static::makeUniqueSlug((string)$m->Name);
            }
        });

        // Refresh slug when Name changes
        static::updating(function (GodownMaster $m) {
            if ($m->isDirty('Name')) {
                $m->slug = static::makeUniqueSlug((string)$m->Name, $m->getKey());
            }
        });
    }

    public static function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        if ($base === '') $base = 'godown';

        $slug = $base;
        $i = 2;

        while (
            static::query()
                ->where('slug', $slug)
                ->where('isDelete', 0)
                ->when($ignoreId, fn($q) => $q->where('godown_id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
