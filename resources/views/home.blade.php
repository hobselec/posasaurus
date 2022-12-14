@extends('layouts.app')

@section('content')
<h2>
    You are signed in as {{ auth()->user()->name }}
</h2>

<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button>Logout</button>
</form>

<two-factor-auth :enabled="{{ json_encode(auth()->user()->twoFactorAuthEnabled()) }}" />
    
@endsection