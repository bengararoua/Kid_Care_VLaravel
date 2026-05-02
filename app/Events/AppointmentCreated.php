<?php

namespace App\Events;

use App\Models\Appointment;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AppointmentCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    public $appointment;
    
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }
    
    public function broadcastOn()
    {
        return [
            new PrivateChannel('chat.' . $this->appointment->receiver_id),
            new PrivateChannel('chat.' . $this->appointment->sender_id)
        ];
    }
    
    public function broadcastWith()
    {
        return [
            'appointment' => [
                'id' => $this->appointment->id,
                'title' => $this->appointment->title,
                'scheduled_at' => $this->appointment->scheduled_at,
                'sender_id' => $this->appointment->sender_id,
                'receiver_id' => $this->appointment->receiver_id,
                'type' => $this->appointment->type,
                'duration' => $this->appointment->duration,
                'status' => $this->appointment->status
            ]
        ];
    }
}