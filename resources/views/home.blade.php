@extends('layouts.app')

@section('content')
<div class="content" style="font-family: sans-serif">

<h2>
    You are signed in as {{ auth()->user()->name }}
</h2>


<h2>
    Users
</h2>

@foreach($users as $user)
<table style="min-width: 500px; border: 1px solid #000000; border-collapse: collapse">
    <tr>
    <th>Name</th><th>Username</th><th>Last Login</th>
    </tr>
    <tr>
    <td>{{ $user->name }}</td>
    <td>{{ $user->email }}</td>
    <td>{{ $user->updated_at }}</td>
</tr>
</table>

@endforeach


<two-factor-auth :enabled="{{ json_encode(auth()->user()->twoFactorAuthEnabled()) }}" />

<!--
<form method="POST" action="{{ route('logout') }}">
    @csrf
    <button>Logout</button>
</form>
-->

</div>
@endsection