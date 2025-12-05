<?php
session_start();
header('Content-Type: application/json'); // ⬅️ Garante que o navegador entenda que a resposta é JSON

// --- 1. VERIFICAÇÃO DE AUTENTICAÇÃO E CONEXÃO ---

// Se o usuário não estiver logado, retorna um array vazio (o JS espera um array).
if (!isset($_SESSION['usuario'])) {
    http_response_code(401); // 401: Não Autorizado
    echo json_encode([]);
    exit();
}

$servidor = "localhost";
$user = "root";
$password = "D@vidavi02.abc123";
$bd = "cadlogin2";

// Conexão com o banco de dados
$conn = new mysqli($servidor, $user, $password, $bd);

if ($conn->connect_error) {
    http_response_code(500); // 500: Erro Interno do Servidor
    // Em produção, você logaria o erro. Para o cliente, apenas um erro genérico:
    echo json_encode(["error" => "Falha na conexão com o banco de dados."]);
    exit();
}

// --- 2. BUSCA DO ID DO USUÁRIO ---

$usuario = $_SESSION["usuario"];
$id_usuario = null;

// Use prepared statement para buscar o ID do usuário
$stmt = $conn->prepare("SELECT id_usuario FROM cadlogin2 WHERE Usuario = ?");
if ($stmt) {
    $stmt->bind_param("s", $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $id_usuario = $row['id_usuario'] ?? null;
    $stmt->close();
}

// Se o usuário não for encontrado no banco (o que não deveria acontecer se estiver logado)
if (!$id_usuario) {
    http_response_code(404); // 404: Não Encontrado
    echo json_encode([]); // Retorna array vazio para não quebrar o JS
    $conn->close();
    exit();
}

// --- 3. BUSCA DO HISTÓRICO DE IMC ---

$stmt = $conn->prepare("
    SELECT 
        data_calculo, 
        peso_kg, 
        altura_m, 
        imc, 
        classificacao
    FROM historico_imc
    WHERE id_usuario = ?
    ORDER BY data_calculo DESC
");

$historico = [];

if ($stmt) {
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    // Recupera todos os resultados em um array
    while($linha = $result->fetch_assoc()) {
        $historico[] = $linha;
    }
    $stmt->close();
}
// Se a preparação da consulta de histórico falhar, $historico será um array vazio.

// --- 4. RETORNO DO RESULTADO ---

// Retorna o array de histórico (pode ser vazio, se não houver registros)
echo json_encode($historico);

$conn->close();
?>