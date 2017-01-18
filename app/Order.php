<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
	protected $guarded = [];

	public static function forTickets($tickets, $email)
	{
		$order = self::create([
			'email' => $email,
			'amount' => $tickets->sum('price')
		]);

		foreach ($tickets as $ticket)
		{
			$order->tickets()->save($ticket);
		}

		return $order;
	}

	/**
	 * An order belongs to a single concert.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
	 */
	public function concert()
	{
		return $this->belongsTo(Concert::class);
	}

	/**
	 * An order has many tickets.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function tickets()
	{
		return $this->hasMany(Ticket::class);
	}

	/**
	 * Get the ticket quantity for this order.
	 *
	 * @return mixed
	 */
	public function ticketQuantity()
	{
		return $this->tickets()->count();
	}

	/**
	 * Releases the tickets allocated to this
	 * order and cancels this order
	 */
	public function cancel()
	{
		foreach ($this->tickets as $ticket)
		{
			$ticket->release();
		}

		$this->delete();
	}

	public function toArray()
	{
		return [
			'email' => $this->email,
			'ticket_quantity' => $this->ticketQuantity(),
			'amount' => $this->amount,
		];
	}
}
