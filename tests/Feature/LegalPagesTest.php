<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_legal_pages_are_publicly_accessible(): void
    {
        foreach ([
            'legal.about',
            'legal.contact',
            'legal.privacy',
            'legal.distance-sales',
            'legal.delivery-returns',
        ] as $name) {
            $this->get(route($name))->assertOk();
        }
    }

    public function test_contact_page_shows_required_merchant_details(): void
    {
        config()->set('company.type', 'tacir');
        config()->set('company.legal_name', 'Vadexa Yazılım A.Ş.');
        config()->set('company.mersis', '0123456789012345');
        config()->set('company.email', 'info@vadexa.com');
        config()->set('company.chamber', 'İstanbul Ticaret Odası');

        $response = $this->get(route('legal.contact'));

        $response->assertOk();
        $response->assertSee('Ticaret Unvanı');
        $response->assertSee('MERSİS Numarası');
        $response->assertSee('0123456789012345');
        $response->assertSee('KEP Adresi');
        $response->assertSee('info@vadexa.com');
        $response->assertSee('İstanbul Ticaret Odası');
    }
}
