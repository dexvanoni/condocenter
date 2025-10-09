<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Space extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'condominium_id', 'name', 'description', 'photo_path', 'type', 'capacity',
        'price_per_hour', 'requires_approval', 'max_hours_per_reservation',
        'min_hours_per_reservation', 'interval_between_reservations',
        'max_reservations_per_month_per_unit', 'available_from',
        'available_until', 'is_active', 'rules', 'reservation_mode',
        'approval_type', 'prereservation_payment_hours', 'prereservation_auto_cancel',
        'prereservation_instructions'
    ];

    protected $casts = [
        'price_per_hour' => 'decimal:2',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
        'available_from' => 'datetime',
        'available_until' => 'datetime',
        'prereservation_auto_cancel' => 'boolean',
        'prereservation_payment_hours' => 'integer',
    ];

    public function condominium()
    {
        return $this->belongsTo(Condominium::class);
    }

    public function reservations()
    {
        return $this->hasMany(Reservation::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Verificar se o espaço usa pré-reservas
     */
    public function isPrereservation()
    {
        return $this->approval_type === 'prereservation';
    }

    /**
     * Verificar se o espaço tem aprovação automática
     */
    public function isAutomaticApproval()
    {
        return $this->approval_type === 'automatic';
    }

    /**
     * Verificar se o espaço tem aprovação manual
     */
    public function isManualApproval()
    {
        return $this->approval_type === 'manual';
    }

    /**
     * Obter tempo limite para pagamento em horas
     */
    public function getPaymentDeadlineHours()
    {
        return $this->prereservation_payment_hours ?? 24;
    }

    /**
     * Calcular data limite para pagamento
     * O prazo sempre conta a partir do momento atual (agora), não da data do evento
     */
    public function getPaymentDeadline()
    {
        return now()->addHours($this->getPaymentDeadlineHours());
    }

    /**
     * Obter URL da foto do espaço
     */
    public function getPhotoUrl()
    {
        if ($this->photo_path) {
            return asset('storage/' . $this->photo_path);
        }
        
        // Foto padrão baseada no tipo do espaço
        $defaultPhotos = [
            'party_hall' => 'images/defaults/party_hall.jpg',
            'bbq' => 'images/defaults/bbq.jpg',
            'pool' => 'images/defaults/pool.jpg',
            'sports_court' => 'images/defaults/sports_court.jpg',
            'gym' => 'images/defaults/gym.jpg',
            'meeting_room' => 'images/defaults/meeting_room.jpg',
            'other' => 'images/defaults/space.jpg',
        ];
        
        return asset($defaultPhotos[$this->type] ?? 'images/defaults/space.jpg');
    }

    /**
     * Verificar se o espaço tem foto personalizada
     */
    public function hasCustomPhoto()
    {
        return !is_null($this->photo_path);
    }
}
