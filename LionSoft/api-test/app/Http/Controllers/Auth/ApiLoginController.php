<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Lang;
use Validator;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\Response;

class ApiLoginController extends Controller
{
    use AuthenticatesUsers;

    public function __construct(Request $request){
        $request->request->add([
            'email' => $request->username,
            ]);
    }

    protected function validarLogin($request) {
        $validator = Validator::make($request->all(),[
            'username' => 'required|email',
            'password' => 'required|min:6'
        ]);
        return $validator;
    }

    protected function authenticated(Request $request, $user)
    {
        $validator = $this->validarLogin($request);
        if($validator->fails()) {
            return response()->json(['message' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }
        // implement your user role retrieval logic, for example retrieve from `roles` database table

        $role = $user->perfil;

        // grant scopes based on the role that we get previously
        if ($role == 'Administrador') {
            $request->request->add([
                'scope' => 'Administrador'
            ]);
        } elseif ($role == 'Editor') {
            $request->request->add([
                'scope' => 'Editor'
            ]);
        }

        // forward the request to the oauth token request endpoint
        $tokenRequest = Request::create(
            '/oauth/token',
            'post'
        );
        return Route::dispatch($tokenRequest);
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        $validator = $this->validarLogin($request);
        if($validator->fails()) {
            return response()->json(['message' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }
        // $request->session()->regenerate(); // coment this becose api routes with passport failed here.

        $this->clearLoginAttempts($request);

        return $this->authenticated($request, $this->guard()->user())
            ?: response()->json(['message' => 'Erro ao autenticar usuário.', 'class' => 'danger'], Response::HTTP_INTERNAL_SERVER_ERROR);

    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        $validator = $this->validarLogin($request);
        if($validator->fails()) {
            return response()->json(['message' => $validator->errors()], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            "message"=>"Usuário e/ou incorreto(s).",
            "class" => "danger",
            "data"=>[
                "errors"=>[
                    $this->username() => Lang::get('auth.failed'),
                ]
            ]
        ]);
    }
}
