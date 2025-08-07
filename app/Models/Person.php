<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Person extends Model
{
    use HasFactory;

    protected $table = 'persons';

    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'position_id',
        'status_id',
        'login',
        'is_company_employee',
        'creator_id',
    ];

    protected $casts = [
        'is_company_employee' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'creator_id');
    }

    public function createdPersons(): HasMany
    {
        return $this->hasMany(Person::class, 'creator_id');
    }

    // Future relationships (will be uncommented when related models are created)
    // public function position(): BelongsTo
    // {
    //     return $this->belongsTo(Position::class);
    // }

    // public function status(): BelongsTo
    // {
    //     return $this->belongsTo(Status::class);
    // }

    // public function phones(): HasMany
    // {
    //     return $this->hasMany(Phone::class);
    // }

    // public function emails(): HasMany
    // {
    //     return $this->hasMany(Email::class);
    // }

    // public function legalEntities(): BelongsToMany
    // {
    //     return $this->belongsToMany(LegalEntity::class, 'person_legal_entity')
    //                  ->withPivot('relationship_type')
    //                  ->withTimestamps();
    // }

    // public function driverCards(): HasMany
    // {
    //     return $this->hasMany(DriverCard::class);
    // }

    // public function tasks(): BelongsToMany
    // {
    //     return $this->belongsToMany(Task::class, 'person_task')->withTimestamps();
    // }

    // public function comments(): HasMany
    // {
    //     return $this->hasMany(Comment::class);
    // }

    // public function files(): HasMany
    // {
    //     return $this->hasMany(File::class);
    // }

    // public function curators(): BelongsToMany
    // {
    //     return $this->belongsToMany(Person::class, 'person_curator', 'person_id', 'curator_id')
    //                  ->withTimestamps();
    // }

    // public function curatedPersons(): BelongsToMany
    // {
    //     return $this->belongsToMany(Person::class, 'person_curator', 'curator_id', 'person_id')
    //                  ->withTimestamps();
    // }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return "{$this->last_name} {$this->first_name} {$this->middle_name}";
    }

    public function getShortNameAttribute(): string
    {
        $firstInitial = mb_substr($this->first_name, 0, 1);
        $middleInitial = mb_substr($this->middle_name, 0, 1);
        return "{$this->last_name} {$firstInitial}.{$middleInitial}.";
    }

    // Scopes
    public function scopeCompanyEmployees($query)
    {
        return $query->where('is_company_employee', true);
    }

    public function scopeByStatus($query, int $statusId)
    {
        return $query->where('status_id', $statusId);
    }

    public function scopeByCreator($query, int $creatorId)
    {
        return $query->where('creator_id', $creatorId);
    }

    public function scopeWithLogin($query)
    {
        return $query->whereNotNull('login');
    }

    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('middle_name', 'like', "%{$search}%");
        });
    }
}
