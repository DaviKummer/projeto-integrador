<?php
// Desativar a exibição de erros na produção por segurança
// ini_set('display_errors', 0);

// --- Configurações de Conexão ---
$servidor = "localhost";
$user = "root";
$password = "D@vidavi02.abc123";
$bd = "cadlogin2"; 
$login_url = "paginainicialhtml.html"; // URL da sua tela de login

// Cria a conexão
$conn = new mysqli($servidor, $user, $password, $bd);

// Verifica a conexão
if ($conn->connect_error) {
    echo "<p style='color:red; text-align:center; font-size:25px;'>Erro de conexão: " . $conn->connect_error . "</p>";
    echo "<h2 style = 'color:blue; text-align:center; font-size:25px'><a href='cadastro.html'>VOLTAR</a></h2>";
    exit(); 
}

// Verifica se o formulário foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // --- 1. RECUPERA OS VALORES DO FORMULÁRIO ---
    $usuario = $_POST["usuario"];
    $email = $_POST["email"]; 
    $senha = $_POST["senha"];
    $confirmasenha = $_POST["confirmaSenha"];

    // --- 2. VERIFICAÇÃO INICIAL: Senhas Coincidem (CORRIGIDO PARA SER CASE-SENSITIVE) ---
    if ($senha !== $confirmasenha) { 
        echo "<p style='color:blue; text-align:center; font-size:25px;'>As senhas não coincidem!</p>";
        echo "<h2 style = 'color:blue; text-align:center; font-size:25px'><a href='cadastro.html'>VOLTAR AO CADASTRO</a></h2>";
        $conn->close();
        exit();
    }
    
    // --- 3. VERIFICAÇÃO DE DUPLICIDADE (Usuário OU Email) USANDO PREPARED STATEMENT ---
    // Seleciona todos os campos, incluindo a Senha (hash) para a nova verificação.
    $stmt = $conn->prepare("SELECT Usuario, Email, Senha FROM cadlogin2 WHERE Usuario = ? OR Email = ?");
    $stmt->bind_param("ss", $usuario, $email);
    $stmt->execute();
    $retorno = $stmt->get_result();
    $row = $retorno->fetch_assoc();
    $stmt->close();

    if ($row) {
        
        // --- 3.1. NOVA VERIFICAÇÃO: Usuário, Email E Senha idênticos a um registro existente? ---
        // Verifica se o Usuário E Email são idênticos (case-insensitive) E se a Senha em texto claro (input)
        // corresponde ao hash da senha (banco de dados).
        if (strcasecmp($row['Usuario'], $usuario) === 0 && 
            strcasecmp($row['Email'], $email) === 0 && 
            password_verify($senha, $row['Senha'])) { 
                
            // Usuário JÁ CADASTRADO com as mesmas credenciais (Usuário, Email, Senha)
            echo "<p style='color:green; text-align:center; font-size:25px;'>Visitante já cadastrado!</p>";
            // Direciona para a tela de Login conforme solicitado
            echo "<h2 style = 'color:blue; text-align:center; font-size:25px'><a href='{$login_url}'>Retornar a Tela de Login</a></h2>";
            $conn->close();
            exit();
        }

        // --- 3.2. VERIFICAÇÃO DE DUPLICIDADE (APENAS Usuário OU Email) ---
        // Se cair aqui, o usuário OU email existe, mas a senha é diferente, 
        // ou só um dos campos (usuário ou email) existe.
        $msg_erro = "";
        
        // Verifica se o usuário já existe
        if (strcasecmp($row['Usuario'], $usuario) === 0) { 
            $msg_erro = "Usuário já existe";
        } 
        
        // Verifica se o email já existe
        if (strcasecmp($row['Email'], $email) === 0) {
            // Se já definiu a mensagem de usuário, adiciona o email na mensagem
            if ($msg_erro) {
                 $msg_erro .= " e Email já cadastrado";
            } else {
                 $msg_erro = "Email já cadastrado";
            }
        }
        
        echo "<p style='color:red; text-align:center; font-size:25px;'>{$msg_erro}</p>";
        echo "<h2 style = 'color:blue; text-align:center; font-size:25px'><a href='cadastro.html'>VOLTAR AO CADASTRO</a></h2>";

    } else {
        
        // --- 4. INSERÇÃO SEGURA DO NOVO USUÁRIO ---
        $hashsenha = password_hash($senha, PASSWORD_BCRYPT);
        
        $stmt_insert = $conn->prepare("INSERT INTO cadlogin2 (Usuario, Email, Senha) VALUES (?, ?, ?)");
        
        $stmt_insert->bind_param("sss", $usuario, $email, $hashsenha);
        
        if ($stmt_insert->execute()) {
            echo "<p style='color:green; text-align:center; font-size:25px;'>Cadastro realizado!</p>";
            echo "<h2 style = 'color:blue; text-align:center; font-size:25px'><a href='{$login_url}'>VOLTAR A TELA DE LOGIN</a></h2>";
        } else {
            echo "ERRO AO CADASTRAR USUÁRIO: " . $stmt_insert->error;
            echo "<h2 style = 'color:blue; text-align:center; font-size:25px'><a href='cadastro.html'>VOLTAR AO CADASTRO</a></h2>";
        }
        
        $stmt_insert->close();
    }
}

$conn->close();
?>