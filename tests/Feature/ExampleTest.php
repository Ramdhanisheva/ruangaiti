<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;
    public function test_homepage_loads(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_search_page_loads(): void
    {
        $response = $this->get('/search?q=Laravel');
        $response->assertStatus(200);
    }

    public function test_post_detail_page_loads(): void
    {
        $response = $this->get('/post/mastering-laravel-in-2026-modern-patterns-performance');
        $response->assertStatus(200);
    }

    public function test_category_page_loads(): void
    {
        $response = $this->get('/category/technology');
        $response->assertStatus(200);
    }

    public function test_user_page_loads(): void
    {
        $response = $this->get('/user/admin');
        $response->assertStatus(200);
    }

    public function test_tag_page_loads(): void
    {
        $response = $this->get('/tag/Laravel');
        $response->assertStatus(200);
    }

    public function test_login_page_loads(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_signup_page_loads(): void
    {
        $response = $this->get('/signup');
        $response->assertStatus(200);
    }
}
