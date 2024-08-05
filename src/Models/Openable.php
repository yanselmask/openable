<?php

namespace Yanselmask\Openable\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Openable extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'data',
        'is_actived',
        'is_default'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data' => 'array',
            'is_actived' => 'boolean',
            'is_default' => 'boolean'
        ];
    }
    /**
     * {@inheritdoc}
     */
    protected $observables = [
        'created',
    ];
    /**
     * The default rules that the model will validate against.
     *
     * @var array
     */
    protected $rules = [
        'data' => 'required|array',
        'is_actived' => 'nullable|boolean',
        'is_default' => 'nullable|boolean',
    ];
    /**
     * Whether the model should throw a
     * ValidationException if it fails validation.
     *
     * @var bool
     */
    protected $throwValidationExceptions = true;

    public function __construct(array $data = [])
    {
        $this->setTable(config('openable.database_name'));
        parent::__construct($data);
    }

    /**
     * @TODO: refactor
     *
     * {@inheritdoc}
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if($model->is_default) {
                $last = Openable::where('openable_id', $model->openable_id)
                    ->where('openable_type', $model->openable_type)
                    ->where('is_default', true)
                    ->first();
                if(!is_null($last))
                {
                    $last->update([
                        'is_default' => false,
                    ]);
                }
            }
        });
    }

    public function openable()
    {
        return $this->morphTo();
    }

}
