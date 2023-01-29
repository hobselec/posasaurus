<?php
 
namespace App\Events;
 
use App\Models\User;
use App\Models\Ticket;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
 
class UpdateBilling implements ShouldBroadcast
{
    use SerializesModels;
 
    /**
     * The user that created the server.
     *
     * @var \App\Models\User
     */
    public $balance;
 
    /**
     * Create a new event instance.
     *
     * @param  array  $balance
     * @return void
     */
    public function __construct(array $balance)
    {
        $this->balance = $balance;
    }
 
    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-billing');
    }

   // public function broadcastWhen()
    //{

    //    return $this->balance->payment_type != null;
   //}
}