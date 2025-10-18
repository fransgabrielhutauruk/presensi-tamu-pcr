<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct()
    {
        $this->activeRoot = '';
    }

    public function index()
    {
        $this->title = 'Event';
        $this->activeMenu = 'event';
        $this->breadCrump[] = ['title' => 'Event', 'link' => url()->current()];

        $this->dataView([]);

        return $this->view('admin.event.list');
    }
}
