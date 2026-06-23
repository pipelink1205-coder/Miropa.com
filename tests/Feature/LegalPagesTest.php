<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_legal_pages_are_publicly_accessible(): void
    {
        $this->get('/como-funciona')->assertOk()->assertInertia(fn ($p) => $p->component('Legal/HowItWorks'));
        $this->get('/terminos')->assertOk()->assertInertia(fn ($p) => $p->component('Legal/Terms'));
        $this->get('/privacidad')->assertOk()->assertInertia(fn ($p) => $p->component('Legal/Privacy'));
    }
}
