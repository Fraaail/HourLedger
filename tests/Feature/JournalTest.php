<?php

use App\Models\Journal;
use App\Models\TimeEntry;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;

test('can save a journal entry', function () {
    $date = '2023-10-10';
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->post("/journal/{$date}", ['content' => 'Worked on the project.']);

    $response->assertStatus(302);
    $this->assertDatabaseHas('journals', [
        'date' => $date,
        'content' => 'Worked on the project.',
    ]);
});

test('can save a journal entry via ajax', function () {
    $date = '2023-10-11';
    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->postJson("/journal/{$date}", ['content' => 'Async save.']);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'message' => 'Journal saved.',
        'redirect' => route('calendar', [], false),
    ]);

    $this->assertDatabaseHas('journals', [
        'date' => $date,
        'content' => 'Async save.',
    ]);
});

test('can update a journal entry', function () {
    $date = '2023-10-10';
    Journal::create(['date' => $date, 'content' => 'Old content.']);

    $response = $this->withoutMiddleware(ValidateCsrfToken::class)
        ->post("/journal/{$date}", ['content' => 'New content.']);

    $response->assertStatus(302);
    $this->assertDatabaseHas('journals', [
        'date' => $date,
        'content' => 'New content.',
    ]);
});

test('calendar view includes journal data', function () {
    $date = now()->format('Y-m-d');
    Journal::create(['date' => $date, 'content' => 'My test activity']);

    $response = $this->get('/calendar');
    $response->assertStatus(200);
    $response->assertSee('My test activity', false);
});

test('calendar view marks missing journal nicely', function () {
    $dateStr = now()->setDay(1)->format('Y-m-d');
    TimeEntry::create([
        'date' => $dateStr,
        'time_in' => now()->startOfDay()->addHours(9),
        'time_out' => now()->startOfDay()->addHours(17),
        'total_minutes' => 480,
    ]);

    $response = $this->get('/calendar');
    $response->assertStatus(200);
    $response->assertSee('missing-journal-indicator', false);
});
