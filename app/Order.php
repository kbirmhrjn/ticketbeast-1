<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
	protected $guarded = [];

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
}
