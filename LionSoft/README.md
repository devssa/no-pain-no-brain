# LionSoft

Teste de programador PHP, desenvolvendo uma api RESTful Laravel para empresa LionSoft.

## Getting Started

As instruções a seguir auxiliam na configuração e utilização do projeto. Por favor, siga atentamente. 

### Installing

Se o projeto já estiver descompactado, entre no arquivo .env do mesmo e configure as linhas a seguir de acordo com seu banco de dados.

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=api_test
DB_USERNAME=root
DB_PASSWORD=root
```

Com o banco de dados configurado em seu projeto, rode as migrations com o comando a seguir, gerando as tabelas e o login de administrador inicial:
```
php artisan migrate --seed
```

Depois execute o seguinte comando para obter o client_id e client_secret da sua aplicação, digitando o nome da aplicação cliente que utilizará:

```
php artisan passport:client --password
```

Salve os dados gerados no arquivo clients no diretório api-test, vamos precisar para realizar as autenticações.

Para testar a aplicação, execute:
```
php artisan serve
```
E depois abra no postman o diretório: https://www.getpostman.com/collections/a5bd27e5d08517d28f24

Abra a requisição Auth/Login e substitua as seguintes linhas pelo client gerado anteriormente:

```
"client_id": "1",
"client_secret": "5SZdVlpGsVb7zRc3JHCBsM8SN6PyhdvFUi1hWkVh",
```

O login de administrador já esta configurado, então basta criar usuários do tipo Editor em User/Signup e utilizar a aplicação.

Em cada resquest em que contem o header Authorization, substitua pelo token de sessão gerado, mantendo o Bearer + espaço. Lembrando que o projeto roda por nível de acesso, assim como especificado na documentação enviada, recuperando o scope (coluna perfil) no banco de dados da tabela users.

## Version

For the versions available, see the [tags on this repository](https://github.com/abrahimpatrick/lionsoft/tags). 

## Author

* **Patrick Abrahim** - *Initial work* - [AbrahimPatrick](https://github.com/abrahimpatrick)

