@extends('layouts.main')
@section('content')
<section class="general-content">
        <header>
            <h1>Users Details for <strong>{{ $user->username }}</h1>
        </header>
        <article>
            <div>
                <span><strong>Username:</strong></span>
                <span>{{ $user->username }}</a></span>
            </div>
            <div>
                <span><strong>Email:</strong></span>
                @if(Auth::id() == $user->id || isAdmin())
                <span>{{ $user->email }}</a>(Only visible to you)</span>
                @else
                <span><i>(hidden)</i></span>
                @endif
            </div>
            <div>
                <span><strong>Status:</strong></span>
                <span>{{ ($user->status==0?"Not Active":"Active") }}</a></span>
            </div>
            <div>
                <span><strong>Created:</strong></span>
                <span>{{ Carbon\Carbon::createFromTimestamp($user->created)->diffForHumans() }}</a></span>
            </div>
            <div>
                <span><strong>Access:</strong></span>
                <span>{{ Carbon\Carbon::createFromTimestamp($user->access)->diffForHumans() }}</a></span>
            </div>
        </article>
</section>
<!-- /.container-fluid-->
<!-- /.content-wrapper-->
@endsection