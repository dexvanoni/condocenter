<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConversationWebController extends Controller
{
    public function announcementForm(Request $request)
    {
        return view('conversations.announcement');
    }
}
