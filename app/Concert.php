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
		return $this->belongsToMany(Order::class, 'tickets');
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
		$tickets = $this->findTickets($ticketQuantity);

		return $this->createOrder($email, $tickets);
	}

	public function findTickets($quantity)
	{
		$tickets = $this->tickets()->available()->take($quantity)->get();

		if ($tickets->count() < $quantity)
		{
			throw new NotEnoughTicketsRemainException;
		}

		return $tickets;
	}

	public function createOrder($email, $tickets)
	{
		return Order::forTickets($tickets, $email);
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
