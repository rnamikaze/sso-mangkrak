<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name') }} - Autentikasi</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap"
        rel="stylesheet">

    <script src="https://kit.fontawesome.com/c036512f7c.js" crossorigin="anonymous"></script>

    <!-- Styles -->
    <link href="{{ asset('/css/app.css') }}" rel="stylesheet">

    <style>
        .noto-sans-400 {
            font-family: "Lato", sans-serif;
            font-weight: 400;
            font-style: normal;
        }

        .card-header {
            background: linear-gradient(to right, #76c7a6, #19a680);

            color: white;
            font-weight: bold;
            padding: 10px 15px;
            text-align: center;
            text-transform: uppercase;
        }

        .card-body {
            padding: 10px 15px;
            text-align: center;
            background: #ffffff;
            color: #2f2f2f;
        }

        .passport-authorize .container {
            margin-top: 30px;
        }

        .passport-authorize .scopes {
            margin-top: 20px;
        }

        .passport-authorize .buttons {
            display: flex;
            /* width: 100%; */
            justify-content: space-around;
            padding: 10px 15px 5px 15px;
            flex-direction: column;
            /* background: red; */
        }

        .passport-authorize .btn {
            width: 125px;
        }

        .passport-authorize .btn-approve {
            margin-right: 15px;
        }

        .passport-authorize form {
            display: inline;
        }

        .my-button {
            width: 100%;
            padding: 10px 15px;
            font-size: 17px;
            background: linear-gradient(to right, #76c7a6, #19a680);
            border: 1px solid rgba(255, 255, 255, 0.45);
            color: white;
            font-weight: bold;

            display: flex;
            justify-content: center;

            border-radius: 4px;
            margin-bottom: 10px;
            cursor: pointer;
        }

        /* HTML: <div class="loader"></div> */
        #loader-1 {
            width: 90px;
            aspect-ratio: 1;
            border-radius: 50%;
            background:
                radial-gradient(farthest-side, #42b491 94%, #0000) top/8px 8px no-repeat,
                conic-gradient(#0000 30%, #42b491);
            -webkit-mask: radial-gradient(farthest-side, #0000 calc(100% - 8px), #000 0);
            animation: l13 1s infinite ease-in-out;
        }

        @keyframes l13 {
            100% {
                transform: rotate(1turn)
            }
        }
    </style>
</head>

<body class="passport-authorize noto-sans-400"
    style="background: #edf2f7; color: white; display: flex; justify-content: center; width: 100vw; height: 100vh; align-items: center; position: relative;">
    <div class="container" style="border: 1px solid whitesmoke; border-radius: 8px; overflow: hidden;">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card card-default">
                    <div style="display: none;" class="card-header noto-sans-400">
                        Izin Autentikasi
                    </div>
                    <div
                        style="flex-direction: column; color: #19a680; margin-bottom: 50px; font-size: 13px; display: flex; justify-content: center; align-items: center;">
                        <div
                            style="display: flex; position: relative; left: -5px; justify-content: center; align-items: center; width: 100px; height: 100px;">
                            <div
                                style="font-size: 18px; position: absolute; width: 100%; height: 100%; display: flex; justify-content: center; align-items: center;">
                                {{-- <i class="fa-solid fa-key"></i> --}}
                                <img style="opacity: 0.7;" width="85px" height="auto"
                                    src="https://storage.unusida.id/storage/images/sso/sso-logo-green-cool-1.svg"
                                    alt="UNUSIDA Single sign on logo" />
                            </div>

                            <div id="loader-1"></div>
                        </div>
                        {{-- <div style="margin-top: 5px;  letter-spacing: 2px; color: gray;">
                            Menyambungkan . . . .
                        </div> --}}
                        {{-- <i class="fa-brands fa-cloudsmith fa-flip"></i> --}}
                    </div>

                    <div style="display: none;" class="card-body">
                        <!-- Introduction -->
                        <p><strong style="color: #19a680">{{ $client->name }}</strong><br /> meminta izin untuk
                            melanjutkan.
                        </p>

                        <!-- Scope List -->
                        @if (count($scopes) > 0)
                            <div class="scopes">
                                <p><strong>This application will be able to:</strong></p>

                                <ul>
                                    @foreach ($scopes as $scope)
                                        <li>{{ $scope->description }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="buttons">
                            <!-- Authorize Button -->
                            <form id="auto-approve" method="post"
                                action="{{ route('passport.authorizations.approve') }}">
                                @csrf

                                <input type="hidden" name="state" value="{{ $request->state }}">
                                <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                                <button style="display: none;" type="submit" class="my-button">Lanjutkan</button>
                            </form>

                            <!-- Cancel Button -->
                            <form method="post" action="{{ route('passport.authorizations.deny') }}">
                                @csrf
                                @method('DELETE')

                                <input type="hidden" name="state" value="{{ $request->state }}">
                                <input type="hidden" name="client_id" value="{{ $client->getKey() }}">
                                <input type="hidden" name="auth_token" value="{{ $authToken }}">
                                <button style="display: none;" class="my-button" style="opacity: 0.7">Batal</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div
        style="position: fixed; font-weight: bold; font-size: 14px; line-height: 1.4em; color: #19a680; display: flex; flex-direction: column; justify-content: center; align-items: center; bottom: 20px; left: 0px; width: 100vw; height: max-content;">

        <div style="color: #464646; font-size: 18px; margin-bottom: 5px;"><span style=" letter-spacing: 2px;">U<span
                    style="color: #19a680; ">NU</span>SIDA</span>
        </div>
        <span style="letter-spacing: 1px; border-radius: 4px;  color: rgb(82, 82, 82); padding: 0px 7px;">SINGLE
            S1GN ON &#169;2024</span>
    </div>

    <script>
        // Wait for the document to be fully loaded
        document.addEventListener("DOMContentLoaded", function() {
            // Set a timeout to submit the form after 5 seconds (5000 milliseconds)
            setTimeout(function() {
                document.getElementById('auto-approve').submit();
            }, 1000); // Change 1000 to your desired timeout in milliseconds
        });
    </script>
</body>

</html>
