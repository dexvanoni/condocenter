<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RecurringReservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'condominium_id',
        'space_id',
        'created_by',
        'title',
        'description',
        'days_of_week',
        'start_time',
        'end_time',
        'start_date',
        'end_date',
        'status',
        'admin_notes',
    ];

    protected $casts = [
        'days_of_week' => 'array',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relacionamentos
    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function space()
    {
        return $this->belongsTo(Space::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    // Métodos auxiliares
    public function isActive()
    {
        return $this->status === 'active' && 
               $this->start_date <= now()->toDateString() && 
               $this->end_date >= now()->toDateString();
    }

    public function getDaysOfWeekNames()
    {
        $days = [
            0 => 'Domingo',
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
        ];

        return array_map(function($day) use ($days) {
            return $days[$day];
        }, $this->days_of_week);
    }

    public function getFormattedDays()
    {
        $dayNames = $this->getDaysOfWeekNames();
        return implode(', ', $dayNames);
    }

    public function generateReservations()
    {
        $reservations = [];
        $current = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);

        while ($current->lte($end)) {
            if (in_array($current->dayOfWeek, array_map('intval', $this->days_of_week))) {
                $reservations[] = [
                    'space_id' => $this->space_id,
                    'unit_id' => $this->creator->unit_id ?? $this->creator->condominium->units()->first()?->id, // Usar unit_id do criador ou primeira unidade do condomínio
                    'user_id' => $this->created_by,
                    'reservation_date' => $current->toDateString(),
                    'start_time' => $this->start_time,
                    'end_time' => $this->end_time,
                    'status' => 'approved',
                    'notes' => $this->title . ' - ' . $this->description,
                    'recurring_reservation_id' => $this->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $current->addDay();
        }

        return $reservations;
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                    ->where('start_date', '<=', now()->toDateString())
                    ->where('end_date', '>=', now()->toDateString());
    }
}
