<?php
require('fpdf/fpdf.php');
include 'conexao.php';

$sql = "
SELECT c.nome AS colaborador_nome, m.id AS material_id, m.nome AS material_nome
FROM colaboradores c
JOIN colaborador_materiais cm ON cm.colaborador_id = c.id
JOIN materiais m ON m.id = cm.material_id
ORDER BY c.nome, m.nome
";

$result = $conn->query($sql);

$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Relatório de Materiais Associados aos Colaboradores', 0, 1, 'C');

$pdf->SetFont('Arial', 'B', 12);
// Remove a célula de ID colaborador
$pdf->Cell(70, 10, 'Nome Colaborador', 1);
$pdf->Cell(50, 10, 'Nome Material', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);

while ($row = $result->fetch_assoc()) {
    $pdf->Cell(70, 10, utf8_decode($row['colaborador_nome']), 1);
    $pdf->Cell(50, 10, utf8_decode($row['material_nome']), 1);
    $pdf->Ln();
}

$pdf->Output();
?>
