<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Individual (Physical Person) Model
 *
 * @property string $uid
 * @property string $first_name
 * @property string $last_name
 * @property string $middle_name
 * @property int|null $position_id
 * @property int $status_id
 * @property string|null $login
 * @property bool $is_company_employee
 * @property string|null $creator_uid
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 *
 * // Computed attributes
 * @property-read string $full_name
 * @property-read string $short_name
 *
 * // Relationships
 * @property-read Individual|null $creator
 * @property-read \Illuminate\Database\Eloquent\Collection|Individual[] $createdPersons
 *
 * // Scopes
 * @method static \Illuminate\Database\Eloquent\Builder|Individual companyEmployees()
 * @method static \Illuminate\Database\Eloquent\Builder|Individual byStatus(int $statusId)
 * @method static \Illuminate\Database\Eloquent\Builder|Individual byCreator(int $creatorId)
 * @method static \Illuminate\Database\Eloquent\Builder|Individual withLogin()
 * @method static \Illuminate\Database\Eloquent\Builder|Individual search(string $search)
 */
class Individual extends Model
{
    use HasFactory;

    protected $table = 'individual';

    protected $primaryKey = 'uid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uid',
        'first_name',
        'last_name',
        'middle_name',
        'position_id',
        'status_id',
        'login',
        'is_company_employee',
        'creator_uid',
    ];

    protected $casts = [
        'is_company_employee' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Individual::class, 'creator_uid', 'uid');
    }

    public function createdPersons(): HasMany
    {
        return $this->hasMany(Individual::class, 'creator_uid', 'uid');
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

    public function scopeByCreator($query, string $creatorUid)
    {
        return $query->where('creator_uid', $creatorUid);
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
