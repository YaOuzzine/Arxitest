<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InboxController extends Controller
{
    public function index()
    {
        $messages = $this->getMessages();
        return view('inbox', compact('messages'));
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        $messages = $this->getMessages();

        $filteredMessages = array_filter($messages, function($message) use ($query) {
            return stripos($message['title'], $query) !== false
                || stripos($message['sender'], $query) !== false;
        });

        return response()->json(array_values($filteredMessages));
    }

    private function getMessages()
    {
        // Sample data - in a real app, this would come from your database
        return [
            [
                'id' => 1,
                'title' => 'Contract Review Request',
                'sender' => 'Jane Doe',
                'status' => 'unread',
                'date' => '2024-02-21',
                'priority' => 'high',
                'type' => 'document',
            ],
            [
                'id' => 2,
                'title' => 'Project Proposal Draft',
                'sender' => 'John Smith',
                'status' => 'read',
                'date' => '2024-02-20',
                'priority' => 'medium',
                'type' => 'document',
            ],
        ];
    }
}
