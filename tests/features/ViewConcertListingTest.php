<?php

use App\Concert;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ViewConcertListingTest extends TestCase
{
	use DatabaseMigrations;

    /** @test */
    public function user_can_view_a_published_concert_listing()
	{
		$concert = factory(Concert::class)->states('published')->create([
			'title' => 'The Red Chord',
			'subtitle' => 'with Animosity and Lethargy',
			'date' => Carbon::parse('December 13, 2016 8:00pm'),
			'ticket_price' => 3250,
			'venue' => 'The Mosh Pit',
			'venue_address' => '123 Example Lane',
			'city' => 'Laraville',
			'state' => 'ON',
			'zip' => '17916',
			'additional_information' => 'For additional information call (555) 555-5555',
		]);

		$this->visit('concerts/' . $concert->id);

		$this->see($concert->title)
			 ->see($concert->subtitle)
			 ->see('December 13, 2016')
			 ->see('8:00pm')
			 ->see('32.50')
			 ->see($concert->venue)
			 ->see('Laraville, ON 17916')
			 ->see($concert->additional_information);
	}

	/** @test **/
	public function user_cannot_view_unpublished_concert_listings()
	{
		$concert = factory(Concert::class)->states('unpublished')->create();

		$this->get('concerts/' . $concert->id);

		$this->assertResponseStatus(404);
	}
}
