<!doctype html>
<html lang="en">

<head>
    <title> Password Reset</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .panel {
            width: 400px;
            padding: 20px;
            margin: 20px;
            border-radius: 10px;
            border: 1px solid #ddd;
        }

        .form-control:focus {
            box-shadow: none !important;
        }
    </style>
</head>

<body>
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="text-center">
                <!-- <h3><i class="fa fa-lock fa-4x"></i></h3> -->
                <h2 class="text-center"><b>Reset Password</b></h2>
                <p>You can reset your password here.</p>
                <div class="panel-body" style="text-align: left;">
                    <form action="{{ route('reset-password') }}" method="post" autocomplete="off">
                        @csrf
                        <div class="card shadow">
                            <div class="card-header">
                                <h5 class="card-title"> Change Password </h5>
                            </div>

                            <div class="card-body px-4">
                                @if(session()->has('Success'))
                                <div class="alert alert-success">
                                    {{ session()->get('Success') }}
                                </div>
                                @endif
                                @if(session()->has('Error'))
                                <div class="alert alert-danger">
                                    {{ session()->get('Error') }}
                                </div>
                                @endif

                                @if(isset($tokenExpired) && !session()->has('Success') )
                                <div class="alert alert-danger">
                                    {{ $tokenExpired }}
                                </div>
                                @endif
                                <input type="hidden" name="email" @if(isset($email)) value="{{ $email }} " @endif />
                                <input type="hidden" name="token" @if(isset($token)) value="{{ $token }} " @endif />

                                <div class="form-group py-2">
                                    <label> Password </label>
                                    <input type="password" name="password" class="mt-1 form-control" value="{{ old('password') }}" placeholder="New Password">
                                    @if ($errors->has('password'))
                                    <span class="text-danger" style="font-size: 14px;">{{ $errors->first('password') }}</span>
                                    @endif
                                </div>

                                <div class="form-group py-2">
                                    <label> Confirm Password </label>
                                    <input type="password" name="confirm_password" class="mt-1 form-control" value="{{ old('confirm_password') }}" placeholder="Confirm Password">
                                    @if ($errors->has('confirm_password'))
                                    <span class="text-danger" style="font-size: 14px;">{{ $errors->first('confirm_password') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="card-footer">
                                @if(isset($tokenExpired) && !session()->has('Success'))
                                <input type="submit" class="btn btn-primary" disabled value=" Change Password">
                                @else
                                <input type="submit" class="btn btn-primary" value=" Change Password">

                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript Libraries -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
</body>

</html>