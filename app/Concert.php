<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Exceptions\NotEnoughTicketsRemainException;

class Concert extends Model
{
    protected $guarded = [];

	protected $dates = ['date'];

	/**
	 * Query Scope for concerts that have been published.
	 *
	 * @param $query
	 * @return mixed
	 */
	public static function scopePublished($query)
	{
		return $query->whereNotNull('published_at');
	}

	/**
	 * Format the date.
	 *
	 * @return mixed
	 */
	public function getFormattedDateAttribute()
	{
		return $this->date->format('F j, Y');
	}

	/**
	 * Format the start time of the concert.
	 *
	 * @return mixed
	 */
	public function getFormattedStartTimeAttribute()
	{
		return $this->date->format('g:ia');
	}

	/**
	 * Format the ticket_price into dollars.
	 *
	 * @return string
	 */
	public function getTicketPriceInDollarsAttribute()
	{
		return number_format($this->ticket_price / 100, 2);
	}

	/**
	 * A concert can have many orders.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function orders()
	{
		return $this->hasMany(Order::class);
	}

	/**
	 * Returns whether or not and order exists for a certain customer.
	 *
	 * @param $customerEmail
	 * @return bool
	 */
	public function hasOrderFor($customerEmail)
	{
		return $this->orders()->where('email', $customerEmail)->count() > 0;
	}

	/**
	 * Returns the amount of orders for a certain email.
	 *
	 * @param $customerEmail
	 * @return mixed
	 */
	public function ordersFor($customerEmail)
	{
		return $this->orders()->where('email', $customerEmail)->get();
	}

	/**
	 * A concert has many tickets.
	 *
	 * @return \Illuminate\Database\Eloquent\Relations\HasMany
	 */
	public function tickets()
	{
		return $this->hasMany(Ticket::class);
	}

	/**
	 * Order tickets for this concert.
	 *
	 * @param $email
	 * @param $ticketQuantity
	 * @return Model
	 */
	public function orderTickets($email, $ticketQuantity)
	{
		$tickets = $this->tickets()->available()->take($ticketQuantity)->get();

		if ($tickets->count() < $ticketQuantity)
		{
			throw new NotEnoughTicketsRemainException;
		}

		// Creating the order
		$order = $this->orders()->create([
			'email' => $email
		]);

		foreach ($tickets as $ticket)
		{
			$order->tickets()->save($ticket);
		}

		return $order;
	}

	/**
	 * Add tickets to this concert.
	 *
	 * @param $quantity
	 * @return $this
	 */
	public function addTickets($quantity)
	{
		foreach (range(1, $quantity) as $i)
		{
			$this->tickets()->create([]);
		}

		return $this;
	}

	/**
	 * Determine how many tickets remain for this concert.
	 *
	 * @return mixed
	 */
	public function ticketsRemaining()
	{
		return $this->tickets()->available()->count();
	}
}
