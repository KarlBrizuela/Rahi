@extends('layouts.app')

@section('title', 'Login')

@section('styles')
    <link rel="stylesheet" href="{{ asset('css/login/login.css') }}">
@endsection


@section('content')
<div class="Main">

    

    <form class="Inner-Container" method="POST" action="{{ route('login.submit') }}">

    <div class="Outer-Container">
        <img src="{{ asset('logo/logs.png') }}" alt="Intracode">
        <!-- <h1>Intracode ERP</h1> -->
    </div>

    <h4>Sign In Your Enterprise Account</h4>
        @csrf

        <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" placeholder="name@gmail.com" >
        </div>

        <div class="form-group">
            <label for="password">Password</label>

            <div class="password-container">
              <input type="password" name="password" id="password" placeholder="*******" >

        <button type="button" class="showpass" id="showpassword">
            Show
        </button>

        </div>
</div>

        <button class="FormBtn" type="submit">Sign In</button>

        <div class="form-group">
         <label>Mac Address</label>
         <input type="text" name="macaddress" id="macaddress" placeholder="00:1A:2B:3C:4D:5E" >
        </div>
    </form>

</div>
@endsection


<script>
 document.addEventListener('DOMContentLoaded', function() {

 
const password = document.getElementById('password');
const showpassword = document.getElementById('showpassword');

showpassword.addEventListener('click', function(){
    if(password.type === 'password'){
        password.type = "text";
        this.textContent = "Hide"
    }
    else{
        password.type = "password";
        this.textContent ="show";
    }
});

});

</script>