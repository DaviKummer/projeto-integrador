<?php
// paginainicialphp.php

// Inicia a sessão para podermos armazenar o status de login
session_start();

// --- Configuração de Conexão ---
$servidor = "localhost";
$user = "root";
$password = "D@vidavi02.abc123"; // Sua senha
$bd = "cadlogin2";

$conn = new mysqli($servidor, $user, $password, $bd);

// 1. VERIFICAÇÃO DE CONEXÃO
if ($conn->connect_error) {
    // É uma boa prática usar `exit()` ou `die()` aqui para parar o script
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

// --- Lógica de Login ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. RECUPERA OS TRÊS VALORES DO FORMULÁRIO DE LOGIN
    $usuario_digitado = trim($_POST["usuario"]);
    $email_digitado = trim($_POST["email"]); 
    $senha_digitada = $_POST["senha"];

    // 2. BUSCA A LINHA USANDO USUARIO E EMAIL
    $sql = "SELECT Usuario, Senha FROM cadlogin2 WHERE Usuario = ? AND Email = ?";
    $stmt = $conn->prepare($sql);

    // 2a. VERIFICAÇÃO DA PREPARAÇÃO DA QUERY
    if ($stmt === false) {
        // Se houver um erro na sintaxe SQL ou nos nomes das colunas
        die("Erro na preparação da query SQL: " . $conn->error);
    }

    // 2b. BIND DOS PARÂMETROS E EXECUÇÃO
    // "ss" indica que ambos os parâmetros serão strings
    $stmt->bind_param("ss", $usuario_digitado, $email_digitado);
    
    // 2c. VERIFICAÇÃO DA EXECUÇÃO
    if (!$stmt->execute()) {
        // Se a execução falhar por algum motivo no servidor DB
        die("Erro ao executar a query: " . $stmt->error);
    }
    
    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {
        $linha = $resultado->fetch_assoc();
        $hash_armazenado = $linha['Senha']; 

        // 3. Verificar a senha criptografada (hash)
        if (password_verify($senha_digitada, $hash_armazenado)) {
            
            // --- LOGIN BEM-SUCEDIDO ---
            $_SESSION['logado'] = true;
            $_SESSION['usuario'] = $linha['Usuario'];
            header("Location: telainicial.html");
            exit();
            
        } else {
            // FALHA: Senha incorreta
            header("Location: paginainicialhtml.html?erro=login");
            exit();
        }
    } else {
        // FALHA: Usuário E Email não encontrados juntos
        header("Location: paginainicialhtml.html?erro=login");
        exit();
    }
    
    $stmt->close();
}

$conn->close();
?>