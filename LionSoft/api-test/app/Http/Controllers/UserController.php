<?php

namespace App\Http\Controllers;

use App\Post;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class UserController extends Controller
{
    protected function validarUser($request) {
        $validator = Validator::make($request->all(),[
            'name' => 'required',
            'password' => 'required|min:6',
            'email' => 'required|email',
            'cpf' => 'required|min:11'
        ]);
        return $validator;
    }
    public function index()
    {
        try {
            $users = User::all();
            if($users) { //Verifica se existem users
                return response()->json(['users' => $users], Response::HTTP_FOUND);
            } else {
                return response()->json(['message' => 'Nenhum usuário cadastrado até o momento.', 'class' => 'info'], Response::HTTP_NOT_FOUND);
            }
        } catch (QueryException $e) {
            return response()->json(['message' => 'Erro de conexão com o banco de dados.', 'class' => 'danger', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = $this->validarUser($request);
            if($validator->fails()) {
                return response()->json(['message' => $validator->errors()], Response::HTTP_BAD_REQUEST);
            }
            $dados = $request->all();
            if (!User::where('email', $dados['email'])->orWhere('cpf', $dados['cpf'])->count()) { // Verifica se tem email ou cpf duplicado.
                $dados['password'] = bcrypt($dados['password']);
                $user = User::create($dados);
                return response()->json([
                    'user' => $user,
                    'message' => 'Cadastro realizado com sucesso! Aguarde a liberação do administrador.',
                    'class' => 'success'
                ], Response::HTTP_CREATED);
            } elseif(User::where('email', $dados['email'])->count()) {
                return response()->json(['message' => 'Este e-mail já está cadastrado.', 'class' => 'danger'], Response::HTTP_BAD_REQUEST);
            } else {
                return response()->json(['message' => 'Este cpf já está cadastrado.', 'class' => 'danger'], Response::HTTP_BAD_REQUEST);
            }
        } catch (QueryException $e) {
            return response()->json(['message' => 'Erro de conexão com o banco de dados.', 'class' => 'danger', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {
            $user = User::orderBy('created_at', 'DESC')->with('post')->where('id', $id)->get();
            if($user->count() > 0) {
                return response()->json([
                    'user' => $user,
                ], Response::HTTP_FOUND);
            } else {
                return response()->json(['message' => 'Editor não encontrado.', 'class' => 'danger'], Response::HTTP_NOT_FOUND);
            }
        } catch (QueryException $e) {
            return response()->json(['message' => 'Erro de conexão com o banco de dados.', 'class' => 'danger', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = $this->validarUser($request);
            if($validator->fails()) {
                return response()->json(['message' => $validator->errors()], Response::HTTP_BAD_REQUEST);
            }

            $dados = $request->all();
            $check_user_email = User::where('email', $dados['email'])->get()->first();
            $check_user_cpf = User::where('cpf', $dados['cpf'])->get()->first();

            if(!($check_user_email['email'] == $dados['email'] && $check_user_email['id'] != $id ||
                 $check_user_cpf['cpf'] == $dados['cpf'] && $check_user_cpf['id'] != $id)) {
                $user = User::find($id);
                if ($user) {
                    if (! $request['password'] == '') {
                        $dados = $request->all();
                        $dados['password'] = bcrypt($dados['password']);
                        $user->update($dados);
                    } else {
                        $dados = $request->all()->except(['password']);
                        $user->update($dados);
                    }
                    return response()->json([
                        'user' => $user,
                        'message' => 'Editor atualizado com sucesso',
                        'class' => 'success'
                    ], Response::HTTP_OK);
                } else {
                    return response()->json(['message' => 'Editor não encontrado.', 'class' => 'danger'], Response::HTTP_NOT_FOUND);
                }
            } elseif ($check_user_email['email'] == $dados['email'] && $check_user_email['id'] != $id) {
                return response()->json(['message' => 'Este e-mail já está cadastrado.', 'class' => 'danger'], Response::HTTP_BAD_REQUEST);
            } else {
                return response()->json(['message' => 'Este cpf já está cadastrado.', 'class' => 'danger'], Response::HTTP_BAD_REQUEST);
            }
        } catch (QueryException $e) {
            return response()->json(['message' => 'Erro de conexão com o banco de dados.', 'class' => 'danger', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function userStatus($id)
    {
        try {
            $user = User::find($id);
            if ($user) {
                if ($user->status == 0) {
                    $user->status = 1;
                    $user->update();
                    return response()->json([
                        'user' => $user,
                        'message' => 'Editor liberado.',
                        'class' => 'success'
                    ], Response::HTTP_OK);
                } else {
                    $user->status = 0;
                    $user->update();
                    return response()->json([
                        'user' => $user,
                        'message' => 'Editor bloqueado.',
                        'class' => 'warning'
                    ], Response::HTTP_OK);
                }
            } else {
                return response()->json(['message' => 'Editor não encontrado.', 'class' => 'danger'], Response::HTTP_NOT_FOUND);
            }
        } catch (QueryException $e) {
            return response()->json(['message' => 'Erro de conexão com o banco de dados.', 'class' => 'danger', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::find($id);
            if(!$user) {
                return response()->json(['message' => 'Editor não encontrado.', 'class' => 'danger'], Response::HTTP_NOT_FOUND);
            }
            if(!Post::where('user_id', $id)->count()) {
                $user->delete();
                return response()->json([
                    'message' => 'Editor excluido com sucesso.',
                    'class' => 'success'
                ], Response::HTTP_OK);
            } else {
                return response()->json(['message' => 'Não é possivel deletar um Editor que tenha postagens.', 'class' => 'danger'], Response::HTTP_UNAUTHORIZED);
            }
        } catch (QueryException $e) {
            return response()->json(['message' => 'Erro de conexão com o banco de dados.', 'class' => 'danger', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
