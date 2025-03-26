<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Max-Age: 86400");

include 'DbConnect.php';
$objDb = new DbConnect;
$conn = $objDb->connect();

$method = $_SERVER['REQUEST_METHOD'];
$path = explode('/', $_SERVER['REQUEST_URI']);

switch($path[2]) {
    case "resetPassword":
        if ($method == "PUT") {
            $data = json_decode(file_get_contents('php://input'));
            $sql = "SELECT * FROM tb_links WHERE codigo = :codigo";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':codigo', $data->codigo);
            $stmt->execute();
            $link = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($link) {
                $hashedPassword = password_hash($data->senha, PASSWORD_DEFAULT);
                $sql = "UPDATE tb_funcionario SET senha = :senha WHERE n_registro = :funcionario_id";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':senha', $hashedPassword);
                $stmt->bindParam(':funcionario_id', $link['funcionario_id']);
                if ($stmt->execute()) {
                    $sql = "DELETE FROM tb_links WHERE codigo = :codigo";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':codigo', $data->codigo);
                    $stmt->execute();
                    $response = ['status' => 1, 'message' => 'Senha alterada com sucesso.'];
                } else {
                    $response = ['status' => 0, 'message' => 'Erro ao alterar a senha.'];
                }
            } else {
                $response = ['status' => 0, 'message' => 'Código inválido.'];
            }
            echo json_encode($response);
        }
        break;
    case "newPassword":
        $user = json_decode(file_get_contents('php://input'));
        $hashedPassword = password_hash($user->senha, PASSWORD_DEFAULT);
        $sql = "UPDATE tb_funcionario SET senha = :senha, status = '1' WHERE n_registro = :n_registro";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':n_registro', $user->n_registro);
        $stmt->bindParam(':senha', $hashedPassword);
        if($stmt->execute()) {
            $response = ['status' => 1, 'message' => 'Senha alterada com sucesso.'];
        } else {
            $response = ['status' => 0, 'message' => 'Erro ao alterar a senha.'];
        }
        echo json_encode($response);
        break;
    case "verifyResetCode":
        if ($method == "POST") {
            $data = json_decode(file_get_contents('php://input'));
            $sql = "SELECT * FROM tb_links WHERE codigo = :codigo";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':codigo', $data->codigo);
            $stmt->execute();
            $link = $stmt->fetch(PDO::FETCH_ASSOC);
            $response = $link ? ['valid' => true] : ['valid' => false];
            echo json_encode($response);
        }
        break;
    case "checkEmailExists":
        if ($method == "POST") {
            $data = json_decode(file_get_contents('php://input'));
            $sql = "SELECT n_registro, nome FROM tb_funcionario WHERE email = :email";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':email', $data->email);
            $stmt->execute();
            $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($funcionario);
        }
        break;
    case "storeResetCode":
        if ($method == "POST") {
            $data = json_decode(file_get_contents('php://input'));
            $sql = "INSERT INTO tb_links (email, codigo, funcionario_id) VALUES (:email, :codigo, :funcionario_id)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':email', $data->email);
            $stmt->bindParam(':codigo', $data->codigo);
            $stmt->bindParam(':funcionario_id', $data->funcionario_id);
            $response = $stmt->execute() ? ['status' => 1, 'message' => 'Link created successfully.'] : ['status' => 0, 'message' => 'Failed to create link.'];
            echo json_encode($response);
        }
        break;
    case "funcionario":
        switch($method) {
            case "GET":
                $sql = "SELECT n_registro, nome, email, cpf, funcao, cargo, departamento FROM tb_funcionario";
                if (isset($path[3]) && is_numeric($path[3])) {
                    $sql .= " WHERE cpf = :cpf";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':cpf', $path[3]);
                    $stmt->execute();
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($user) {
                        unset($user['senha']);
                    }
                    echo json_encode($user);
                } else {
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($users as &$user) {
                        unset($user['senha']);
                    }
                    echo json_encode($users);
                }
                break;
            case "POST":
                $user = json_decode(file_get_contents('php://input'));
                $hashedPassword = password_hash($user->senha, PASSWORD_DEFAULT);
                $sql = "INSERT INTO tb_funcionario(n_registro, nome, email, senha, cpf, funcao, cargo, departamento, status) VALUES(:n_registro, :nome, :email, :senha, :cpf, :funcao, :cargo, :departamento, '0')";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':n_registro', $user->n_registro);
                $stmt->bindParam(':nome', $user->nome);
                $stmt->bindParam(':email', $user->email);
                $stmt->bindParam(':senha', $hashedPassword);
                $stmt->bindParam(':cpf', $user->cpf);
                $stmt->bindParam(':funcao', $user->funcao);
                $stmt->bindParam(':cargo', $user->cargo);
                $stmt->bindParam(':departamento', $user->departamento);

                if($stmt->execute()) {
                    $response = ['status' => 1, 'message' => 'Record created successfully.'];
                } else {
                    $response = ['status' => 0, 'message' => 'Failed to create record.'];
                }
                echo json_encode($response);
                break;
            case "PUT":
                $user = json_decode(file_get_contents('php://input'));

                // Base da consulta SQL
                $sql = "UPDATE tb_funcionario SET 
                            n_registro = :n_registro, 
                            nome = :nome, 
                            email = :email, 
                            funcao = :funcao, 
                            cargo = :cargo, 
                            departamento = :departamento";

                // Adicionar a senha se ela estiver presente
                if (!empty($user->senha)) {
                    $sql .= ", senha = :senha";
                }

                $sql .= " WHERE cpf = :cpf";

                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':n_registro', $user->n_registro);
                $stmt->bindParam(':nome', $user->nome);
                $stmt->bindParam(':email', $user->email);
                $stmt->bindParam(':cpf', $user->cpf);
                $stmt->bindParam(':funcao', $user->funcao);
                $stmt->bindParam(':cargo', $user->cargo);
                $stmt->bindParam(':departamento', $user->departamento);

                // Vincular a senha se ela estiver presente
                if (!empty($user->senha)) {
                    $hashedPassword = password_hash($user->senha, PASSWORD_DEFAULT);
                    $stmt->bindParam(':senha', $hashedPassword);
                }

                if($stmt->execute()) {
                    $response = ['status' => 1, 'message' => 'Record updated successfully.'];
                } else {
                    $response = ['status' => 0, 'message' => 'Failed to update record.'];
                }
                echo json_encode($response);
                break;
            case "DELETE":
                $sql = "DELETE FROM tb_funcionario WHERE cpf = :cpf";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':cpf', $path[3]);
                if($stmt->execute()) {
                    $response = ['status' => 1, 'message' => 'Record deleted successfully.'];
                } else {
                    $response = ['status' => 0, 'message' => 'Failed to delete record.'];
                }
                echo json_encode($response);
                break;
        }
        break;
    case "ponto":
        switch($method) {
            case "GET":
                $sql = "SELECT * FROM tb_ponto";
                if(isset($path[3]) && is_numeric($path[3])) {
                    $sql .= " WHERE id_ponto = :id_ponto";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(':id_ponto', $path[3]);
                    $stmt->execute();
                    $ponto = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $ponto = $stmt->fetchAll(PDO::FETCH_ASSOC);
                }

                echo json_encode($ponto);
                break;
            case "POST":
                $ponto = json_decode(file_get_contents('php://input'));
                $sql = "INSERT INTO tb_ponto(id_ponto, funcionario_fk, nome_tipo, data_hora) VALUES(:id_ponto, :funcionario_fk, :nome_tipo, :data_hora)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id_ponto', $ponto->id_ponto);
                $stmt->bindParam(':funcionario_fk', $ponto->funcionario_fk);
                $stmt->bindParam(':nome_tipo', $ponto->nome_tipo);
                $stmt->bindParam(':data_hora', $ponto->data_hora);

                if($stmt->execute()) {
                    $response = ['status' => 1, 'message' => 'Record created successfully.'];
                } else {
                    $response = ['status' => 0, 'message' => 'Failed to create record.'];
                }
                echo json_encode($response);
                break;

            case "PUT":
                $ponto = json_decode(file_get_contents('php://input'));
                $sql = "UPDATE tb_ponto SET funcionario_fk = :funcionario_fk, nome_tipo = :nome_tipo, data_hora = :data_hora WHERE id_ponto = :id_ponto";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':funcionario_fk', $ponto->funcionario_fk);
                $stmt->bindParam(':nome_tipo', $ponto->nome_tipo);
                $stmt->bindParam(':data_hora', $ponto->data_hora);
                $stmt->bindParam(':id_ponto', $ponto->id_ponto);

                if($stmt->execute()) {
                    $response = ['status' => 1, 'message' => 'Record updated successfully.'];
                } else {
                    $response = ['status' => 0, 'message' => 'Failed to update record.'];
                }
                echo json_encode($response);
                break;
            case "DELETE":
                $sql = "DELETE FROM tb_ponto WHERE id_ponto = :id_ponto";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id_ponto', $path[3]);
                if($stmt->execute()) {
                    $response = ['status' => 1, 'message' => 'Record deleted successfully.'];
                } else {
                    $response = ['status' => 0, 'message' => 'Failed to delete record.'];
                }
                echo json_encode($response);
                break;
        }
        break;
    case "login":
        $user = json_decode(file_get_contents('php://input'));
        $sql = "SELECT * FROM tb_funcionario WHERE email = :email OR cpf = :cpf";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $user->email);
        $stmt->bindParam(':cpf', $user->email);
        $stmt->execute();
        $funcionario = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($funcionario && password_verify($user->senha, $funcionario['senha'])) {
            $response = ['status' => 1, 'message' => 'Login successful.', 'funcionario' => $funcionario];
        } else {
            $response = ['status' => 0, 'message' => 'Email, CPF ou senha incorretos.'];
        }
        echo json_encode($response);
        break;
}