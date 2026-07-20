<?php

namespace Modules\Users\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Modules\Users\Http\Requests\FeedbackRequest;

class FeedbackController extends Controller
{
    public function index()
    {

    }

    public function store(FeedbackRequest $request)
    {
        auth()->user()->feedback()->create([
            'text' => $request->text
        ]);

        return response()->json([
            'message' => 'Feedback created successfully.'
        ]);
    }
}