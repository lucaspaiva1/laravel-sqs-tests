<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessWebhook;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function create()
    {

        $data = [
            'title' => 'I am a test',
            'content' => 'I am just testing how this works'
        ];

        ProcessWebhook::dispatch('test')
                      ->onConnection('sqsfifo')
                      ->onQueue('emails.fifo');

        return response()->json(true);
    }
}
