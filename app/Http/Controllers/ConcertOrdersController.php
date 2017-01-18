<?php

namespace App\Http\Controllers;

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Exceptions\NotEnoughTicketsRemainException;
use Illuminate\Http\Request;

class ConcertOrdersController extends Controller
{

	private $paymentGateway;

	public function __construct(PaymentGateway $paymentGateway)
	{
		$this->paymentGateway = $paymentGateway;
	}

	public function store($concertId)
	{
		$concert = Concert::published()->findOrFail($concertId);

		$this->validate(request(), [
			'email' => 'required|email',
			'ticket_quantity' => 'required|integer|min:1',
			'payment_token' => 'required'
		]);

		try {
			// Find some tickets for the customer.
			$tickets = $concert->findTickets(request('ticket_quantity'));

			// Charge the customer for the tickets.
			$this->paymentGateway->charge(request('ticket_quantity') * $concert->ticket_price, request('payment_token'));

			// Create an order for those tickets.
			$order = $concert->createOrder(request('email'), $tickets);

			return response()->json($order, 201);

		} catch (PaymentFailedException $e) {
			return response()->json([], 422);
		} catch (NotEnoughTicketsRemainException $e) {
			return response()->json([], 422);
		}
	}
}
