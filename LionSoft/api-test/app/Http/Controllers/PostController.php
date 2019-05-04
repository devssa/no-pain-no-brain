<?php

namespace App\Http\Controllers;

use App\Post;
use App\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class PostController extends Controller
{
    protected function validarPost($request) {
        $validator = Validator::make($request->all(),[
            'titulo' => 'required',
            'descricao' => 'required'
        ]);
        return $validator;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            if (auth()->user()->status == 1) { //Operação permitida somente após administrador liberar.
                $posts = Post::orderBy('created_at', 'DESC')->with('user')->where('user_id', auth()->user()->id)->get();
                if($posts) { //verifica se existe postagens
                    return response()->json(['posts' => $posts], Response::HTTP_FOUND);
                } else {
                    return response()->json(['message' => 'Nenhum post cadastrado até o momento.', 'class' => 'info'], Response::HTTP_NOT_FOUND);
                }
            } else {
                return response()->json(['message' => 'Agurde liberação do Administrador para executar esta ação.', 'class' => 'danger'], Response::HTTP_UNAUTHORIZED);
            }
        } catch (QueryException $e) {
            return response()->json(['message' => 'Erro de conexão com o banco de dados.', 'class' => 'danger', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $validator = $this->validarPost($request);
            if($validator->fails()) {
                return response()->json(['message' => $validator->errors()], Response::HTTP_BAD_REQUEST);
            }
            if (auth()->user()->status == 1) { //Operação permitida somente após administrador liberar.
                $check_post = Post::where(['titulo' => $request['titulo'], 'user_id' => auth()->user()->id])->get()->first();
                if (!($check_post)) { //Verifica se titulo não esta duplicado

                    $post = Post::create([
                        'titulo' => $request['titulo'],
                        'descricao' => $request['titulo'],
                        'user_id' => auth()->user()->id
                    ]);

                    return response()->json(
                        [
                            'message' => 'Post criado com sucesso.',
                            'class' => 'success',
                            'post' => $post
                        ],
                        Response::HTTP_CREATED
                    );
                } else {
                    return response()->json(
                        [
                            'message' => 'Título duplicado.',
                            'class' => 'danger',
                            'post' => ['titulo' => $request['titulo'], 'descricao' => $request['descricao']]
                        ],
                        Response::HTTP_CREATED
                    );
                }
            } else {
                return response()->json(['message' => 'Agurde liberação do Administrador para executar esta ação.', 'class' => 'danger'], Response::HTTP_UNAUTHORIZED);
            }
        } catch (QueryException $e) {
            return response()->json(['message' => 'Erro de conexão com o banco de dados.', 'class' => 'danger', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Post $post
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            if (auth()->user()->status == 1) { //Operação permitida somente após administrador liberar.
               $post = Post::select()->where('id', $id)->where('user_id', auth()->user()->id)->get()->first();
               if ($post) { // Verifica se existe o post
                    $user = User::find($post['user_id']);
                    return response()->json(['post' => $post, 'Editor' => $user], Response::HTTP_FOUND);
                } else {
                    return response()->json(['message' => 'Post não encontrado.', 'class' => 'danger'], Response::HTTP_NOT_FOUND);
                }
            } else {
                return response()->json(['message' => 'Agurde liberação do Administrador para executar esta ação.', 'class' => 'danger'], Response::HTTP_UNAUTHORIZED);
            }
        } catch (QueryException $e) {
            return response()->json(['message' => 'Erro de conexão com o banco de dados.', 'class' => 'danger', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Post $post
     * @return \Illuminate\Http\Response
     */
    public function edit(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \App\Post $post
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            $validator = $this->validarPost($request);
            if($validator->fails()) {
                return response()->json(['message' => $validator->errors()], Response::HTTP_BAD_REQUEST);
            }
            if (auth()->user()->status == 1) { //Operação permitida somente após administrador liberar.
                $dados = $request->all();
                $check_post = Post::where(['titulo' => $request['titulo'], 'user_id' => auth()->user()->id])->get()->first();

                if (!($check_post['titulo'] == $dados['titulo'] && $check_post['id'] != $id)) { // Verifica se o titulo esta duplicado, liberando apenas se for titulo proprio.
                    $post = Post::select()->where('id', $id)->where('user_id', auth()->user()->id)->get()->first();
                    if ($post) {
                        $post->update([
                            'titulo' => $request['titulo'],
                            'descricao' => $request['descricao'],
                            'user_id' => auth()->user()->id
                        ]);
                        return response()->json(['post' => $post, 'message' => 'Post atualizada com sucesso!', 'class' => 'success'], Response::HTTP_OK);
                    } else {
                        return response()->json(['message' => 'O poste não foi encontrado', 'class' => 'danger'], Response::HTTP_NOT_FOUND);
                    }
                } else {
                    return response()->json(
                        [
                            'message' => 'Título duplicado.',
                            'class' => 'danger',
                            'post' => ['titulo' => $request['titulo'], 'descricao' => $request['descricao']]
                        ],
                        Response::HTTP_BAD_REQUEST
                    );
                }
            } else {
                return response()->json(['message' => 'Agurde liberação do Administrador para executar esta ação.', 'class' => 'danger'], Response::HTTP_UNAUTHORIZED);
            }
        } catch (QueryException $e) {
            return response()->json(['message' => 'Erro de conexão com o banco de dados.', 'class' => 'danger', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Post $post
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            if (auth()->user()->status == 1) { //Operação permitida somente após administrador liberar.
                $post = Post::select()->where('id', $id)->where('user_id', auth()->user()->id)->get()->first();

                if ($post) { // Verifica de post existe.
                    $post->delete();
                    return response()->json(['message' => 'Post excluido com sucesso', 'class' => 'success'], Response::HTTP_OK);
                } else {
                    return response()->json(['message' => 'Post não encontrado', 'class' => 'danger'], Response::HTTP_NOT_FOUND);
                }
            } else {
                return response()->json(['message' => 'Agurde liberação do Administrador para executar esta ação.', 'class' => 'danger'], Response::HTTP_UNAUTHORIZED);
            }
        } catch (QueryException $e) {
            return response()->json(['message' => 'Erro de conexão com o banco de dados.', 'class' => 'danger', 'error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
