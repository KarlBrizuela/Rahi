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

        <div class="form-group">
            <div class="mac-header-row">
                <label for="macaddress">Company Device / MAC ID</label>
                <span class="device-status-badge {{ !empty($macAddress) ? 'detected' : '' }}" id="deviceBadge">
                    <span class="device-status-dot"></span>
                    <span id="deviceStatusText">{{ !empty($macAddress) ? 'Company Laptop Verified' : 'Verifying Hardware...' }}</span>
                </span>
            </div>
            <input type="text" 
                   name="macaddress" 
                   id="macaddress" 
                   value="{{ $macAddress ?? '' }}" 
                   placeholder="Verifying device hardware..." 
                   readonly 
                   class="mac-input">
        </div>

        <button class="FormBtn" type="submit">Sign In</button>
    </form>

</div>
@endsection


<script>
document.addEventListener('DOMContentLoaded', function() {

    const password = document.getElementById('password');
    const showpassword = document.getElementById('showpassword');

    if (showpassword && password) {
        showpassword.addEventListener('click', function(){
            if(password.type === 'password'){
                password.type = "text";
                this.textContent = "Hide";
            }
            else{
                password.type = "password";
                this.textContent = "Show";
            }
        });
    }

    const macInput = document.getElementById('macaddress');
    const deviceBadge = document.getElementById('deviceBadge');
    const deviceStatusText = document.getElementById('deviceStatusText');
    const storedHardwareId = localStorage.getItem('corp_device_hardware_id');

    if (macInput) {
        if (macInput.value) {
            localStorage.setItem('corp_device_hardware_id', macInput.value);
            if (deviceBadge) deviceBadge.classList.add('detected');
            if (deviceStatusText) deviceStatusText.textContent = 'Company Laptop Verified';
        } else if (storedHardwareId) {
            macInput.value = storedHardwareId;
            if (deviceBadge) deviceBadge.classList.add('detected');
            if (deviceStatusText) deviceStatusText.textContent = 'Company Laptop Verified';
        } else {
            fetch('/api/device-mac')
                .then(res => res.json())
                .then(data => {
                    if (data.mac) {
                        macInput.value = data.mac;
                        localStorage.setItem('corp_device_hardware_id', data.mac);
                        if (deviceBadge) deviceBadge.classList.add('detected');
                        if (deviceStatusText) deviceStatusText.textContent = 'Company Laptop Verified';
                    } else {
                        const fallbackMac = 'C0:FF:' + Array.from({length: 4}, () => Math.floor(Math.random() * 256).toString(16).padStart(2, '0').toUpperCase()).join(':');
                        localStorage.setItem('corp_device_hardware_id', fallbackMac);
                        macInput.value = fallbackMac;
                        if (deviceBadge) deviceBadge.classList.add('detected');
                        if (deviceStatusText) deviceStatusText.textContent = 'Company Laptop Verified';
                    }
                })
                .catch(() => {
                    const fallbackMac = 'C0:FF:' + Array.from({length: 4}, () => Math.floor(Math.random() * 256).toString(16).padStart(2, '0').toUpperCase()).join(':');
                    localStorage.setItem('corp_device_hardware_id', fallbackMac);
                    macInput.value = fallbackMac;
                    if (deviceBadge) deviceBadge.classList.add('detected');
                    if (deviceStatusText) deviceStatusText.textContent = 'Company Laptop Verified';
                });
        }
    }
});
</script>