<?php


namespace App;


class Reservation {

	protected $tickets;

	public function __construct($tickets)
	{
		$this->tickets = $tickets;
	}

	/**
	 * Determine the total cost of all 
	 * tickets in this reservation.
	 * 
	 * @return int
	 */
	public function totalCost()
	{
		return $this->tickets->sum('price');
	}
}