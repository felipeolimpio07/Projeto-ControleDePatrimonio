
## Se Ainda Não Funcionar - Versão Simplificada

Caso o código acima não funcione, use esta versão mais simples:

``````php
<?php
require('fpdf/fpdf.php');
include 'conexao.php';

// Recebe filtros
$nome = isset($_GET['nome']) ? $_GET['nome'] : '';
$cpf = isset($_GET['cpf']) ? $_GET['cpf'] : '';

// Consulta simples
$sql = "SELECT id, nome, cpf FROM colaboradores WHERE 1=1 ";

if ($nome !== '') {
    $sql .= "AND nome LIKE '%" . $conn->real_escape_string($nome) . "%' ";
}

if ($cpf !== '') {
    $sql .= "AND cpf LIKE '%" . $conn->real_escape_string($cpf) . "%' ";
}

$sql .= "ORDER BY nome";

$result = $conn->query($sql);

// Criar PDF
$pdf = new FPDF();
$pdf->AddPage();

// Cabeçalho
$pdf->SetFillColor(102, 130, 69);
$pdf->Rect(0, 0, 210, 30, 'F');

$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 12, 'AUCA ENGENHARIA', 0, 1, 'C');
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 8, 'Relatorio de Colaboradores', 0, 1, 'C');

$pdf->Ln(5);
$pdf->SetTextColor(0, 0, 0);

// Tabela
$pdf->SetFillColor(102, 130, 69);
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Arial', 'B', 11);

$pdf->Cell(20, 8, 'ID', 1, 0, 'C', true);
$pdf->Cell(100, 8, 'Nome', 1, 0, 'C', true);
$pdf->Cell(70, 8, 'CPF', 1, 1, 'C', true);

// Dados
$pdf->SetFillColor(240, 240, 240);
$pdf->SetTextColor(0, 0, 0);
$pdf->SetFont('Arial', '', 10);

$fill = false;
$total = 0;

while ($row = $result->fetch_assoc()) {
    $pdf->Cell(20, 7, $row['id'], 1, 0, 'C', $fill);
    $pdf->Cell(100, 7, utf8_decode($row['nome']), 1, 0, 'L', $fill);
    $pdf->Cell(70, 7, $row['cpf'], 1, 1, 'C', $fill);
    $fill = !$fill;
    $total++;
}

// Total
$pdf->Ln(3);
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(0, 6, 'Total: ' . $total . ' colaboradores', 0, 1, 'R');

$conn->close();

// Saída
$pdf->Output('D', 'Relatorio_' . date('Y-m-d') . '.pdf');
?>
