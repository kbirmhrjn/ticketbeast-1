<?php

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PurchaseTicketsTest extends TestCase
{
	use DatabaseMigrations;

	protected function setUp()
	{
		parent::setUp();

		$this->paymentGateway = new FakePaymentGateway;

		$this->app->instance(PaymentGateway::class, $this->paymentGateway);
	}

	private function orderTickets(Concert $concert, $params)
	{
		$this->json('POST', "concerts/{$concert->id}/orders", $params);
	}

	private function assertValidationError($field)
	{
		$this->assertResponseStatus(422);

		$this->assertArrayHasKey($field, $this->decodeResponseJson());
	}

    /** @test **/
    public function customer_can_purchase_concert_tickets()
	{
		// Arrange
		// Create a concert
		$concert = factory(Concert::class)->create(['ticket_price' => 3250]);

		// Act
		// Purchase concert tickets
		$this->orderTickets($concert, [
			'email' => 'john@example.com',
			'ticket_quantity' => 3,
			'payment_token' => $this->paymentGateway->getValidTestToken()
		]);

		// Assert
		$this->assertResponseStatus(201);
		// Make sure the customer was charged the correct amount
		$this->assertEquals(9750, $this->paymentGateway->totalCharges());

		// Make sure that an order exists for this customer
		$order = $concert->orders()->where('email', 'john@example.com')->first();

		$this->assertNotNull($order);

		$this->assertEquals(3, $order->tickets()->count());
    }

    /** @test **/
    public function email_is_required_to_purchase_tickets()
	{
    	$concert = factory(Concert::class)->create();

		$this->orderTickets($concert, [
			'ticket_quantity' => 3,
			'payment_token' => $this->paymentGateway->getValidTestToken()
		]);

		$this->assertValidationError('email');
    }

    /** @test **/
    public function email_must_be_valid_to_purchase_tickets()
	{
    	$concert = factory(Concert::class)->create();

		$this->orderTickets($concert, [
			'email' => 'not-a-valid-email',
			'ticket_quantity' => 3,
			'payment_token' => $this->paymentGateway->getValidTestToken()
		]);

		$this->assertValidationError('email');
    }
    
    /** @test **/
    public function ticket_quantity_is_required_to_purchase_tickets()
	{
    	$concert = factory(Concert::class)->create();

		$this->orderTickets($concert, [
			'email' => 'valid@email.com',
			'payment_token' => $this->paymentGateway->getValidTestToken()
		]);

		$this->assertValidationError('ticket_quantity');
    }

	/** @test **/
	public function ticket_quantity_must_be_at_least_one_to_purchase_tickets()
	{
		$concert = factory(Concert::class)->create();

		$this->orderTickets($concert, [
			'email' => 'valid@email.com',
			'ticket_quantity' => 0,
			'payment_token' => $this->paymentGateway->getValidTestToken()
		]);

		$this->assertValidationError('ticket_quantity');
	}

	/** @test **/
	public function payment_token_is_required_to_purchase_tickets()
	{
		$concert = factory(Concert::class)->create();

		$this->orderTickets($concert, [
			'email' => 'valid@email.com',
			'ticket_quantity' => 3
		]);

		$this->assertValidationError('payment_token');
	}
}
