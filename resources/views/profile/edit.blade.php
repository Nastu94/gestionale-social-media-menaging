<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">                    
                    @include('profile.partials.update-password-form')

                    <div class="mt-2 flex justify-end">
                        <!-- Pulsante di login a Facebook -->
                        <fb:login-button 
                            scope="public_profile,email"
                            onlogin="checkLoginState();">
                        </fb:login-button>
                        <div id="status"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.fbAsyncInit = function() {
            FB.init({
                appId      : '{{ config('services.facebook.client_id') }}',
                cookie     : true,
                xfbml      : true,
                version    : 'v20.0'
            });

            FB.AppEvents.logPageView();   

            FB.getLoginStatus(function(response) {
                statusChangeCallback(response);
            });
        };

        (function(d, s, id){
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) {return;}
            js = d.createElement(s); js.id = id;
            js.src = "https://connect.facebook.net/en_US/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));

        function checkLoginState() {
            FB.getLoginStatus(function(response) {
                statusChangeCallback(response);
            });
        }

        function statusChangeCallback(response) {
            console.log(response);
            if (response.status === 'connected') {
                testAPI();
                // Invia i dati al backend
                axios.post('{{ route('login.facebook.callback') }}', {
                    accessToken: response.authResponse.accessToken,
                    userID: response.authResponse.userID,
                })
                .then(function (response) {
                    console.log(response.data);
                })
                .catch(function (error) {
                    console.log(error);
                });
            } else {
                document.getElementById('status').innerHTML = 'Please log into this app.';
            }
        }

        function testAPI() {
            FB.api('/me', function(response) {
                console.log('Successful login for: ' + response.name);
                document.getElementById('status').innerHTML =
                    'Thanks for logging in, ' + response.name + '!';
            });
        }
    </script>
</x-app-layout>
