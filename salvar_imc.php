<?php
session_start();
header('Content-Type: application/json');

// --- 1. VERIFICAÇÃO DE AUTENTICAÇÃO E DADOS DE ENTRADA ---

// O usuário deve estar logado
if (!isset($_SESSION['usuario'])) {
    http_response_code(401); // Não Autorizado
    echo json_encode(["success" => false, "message" => "Usuário não autenticado."]);
    exit();
}

// Verifica se a requisição é um POST e se contém dados
$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);

if (json_last_error() !== JSON_ERROR_NONE || 
    !isset($data["peso"], $data["altura"], $data["imc"], $data["classificacao"])) {
    http_response_code(400); // Requisição Inválida
    echo json_encode(["success" => false, "message" => "Dados de IMC incompletos ou inválidos."]);
    exit();
}

// Converte os dados para os tipos corretos (Sanitização e Conversão)
// A função floatval() garante que os dados serão passados como números flutuantes
$peso          = floatval($data["peso"]);
$altura        = floatval($data["altura"]);
$imc           = floatval($data["imc"]);
$classificacao = $data["classificacao"]; // String

// --- 2. CONEXÃO E BUSCA DO ID DO USUÁRIO ---

$servidor = "localhost";
$user = "root";
$password = "D@vidavi02.abc123";
$bd = "cadlogin2";

$conn = new mysqli($servidor, $user, $password, $bd);

if ($conn->connect_error) {
    // Log do erro (opcional, mas recomendado)
    // error_log("Erro de conexão com o BD: " . $conn->connect_error);
    http_response_code(500); // Erro Interno do Servidor
    echo json_encode(["success" => false, "message" => "Erro ao conectar ao banco de dados."]);
    exit();
}

$usuario = $_SESSION["usuario"];

// Buscar ID do usuário
$stmt = $conn->prepare("SELECT id_usuario FROM cadlogin2 WHERE Usuario = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Falha na preparação da consulta de ID."]);
    $conn->close();
    exit();
}
$stmt->bind_param("s", $usuario);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$id_usuario = $row['id_usuario'] ?? null; // Usa null se não encontrar o usuário
$stmt->close();

if (!$id_usuario) {
    http_response_code(404);
    echo json_encode(["success" => false, "message" => "ID de usuário não encontrado."]);
    $conn->close();
    exit();
}

// --- 3. INSERÇÃO DOS DADOS DO IMC ---

$stmt = $conn->prepare("
    INSERT INTO historico_imc (id_usuario, data_calculo, peso_kg, altura_m, imc, classificacao)
    VALUES (?, NOW(), ?, ?, ?, ?)
");

// A string de tipos "iddds" está correta se 'peso', 'altura' e 'imc' forem DOUBLEs na tabela.
// Vamos manter, mas a conversão floatval acima já ajuda a prevenir problemas.
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Falha na preparação da consulta de INSERT."]);
    $conn->close();
    exit();
}

// Certifique-se de que a ordem corresponde exatamente aos '?'
$stmt->bind_param("iddds", $id_usuario, $peso, $altura, $imc, $classificacao);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "IMC salvo com sucesso!"]);
} else {
    // Log do erro de execução (útil para depuração)
    // error_log("Erro ao executar INSERT: " . $stmt->error);
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Falha ao salvar IMC."]);
}

$stmt->close();
$conn->close();
?>