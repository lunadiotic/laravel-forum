<?php

namespace App\Http\Controllers;

use App\Discussion;
use App\Reply;
use App\User;
use Illuminate\Http\Request;
use Session;
use Notification;
use App\Notifications\ReplyNotification;

class DiscussionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pages.discuss.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'channel_id' => 'required',
            'title' => 'required',
            'content' => 'required'
        ]);

        $request['user_id'] = $request->user()->id;
        $request['slug'] = str_slug($request->title, '-');

        $discuss = Discussion::create($request->all());

        Session::flash('status', 'Discussion successfully created.');

        return redirect()->route('discussion', ['slug' => $discuss->slug]);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($slug)
    {
        $discuss = Discussion::where('slug', $slug)->first();
        $bestAnswer = $discuss->replies()->where('is_answered', 1)->first();
        return view('pages.discuss.show', compact('discuss', 'bestAnswer'));
    }

    public function reply(Request $request, $id)
    {
        $discuss = Discussion::find($id);

        $this->validate($request, [
            'content' => 'required'
        ]);

        $request['user_id'] = $request->user()->id;
        $request['discussion_id'] = $id;

        Reply::create($request->all());

        $discuss = Discussion::find($id);

        $watchers = [];

        foreach($discuss->watchers as $watcher) {
            array_push($watchers, User::find($watcher->user_id));
        }
        
        Notification::send($watchers, new ReplyNotification($discuss));

        Session::flash('status', 'Discussion successfully replied.');

        return redirect()->back();

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
