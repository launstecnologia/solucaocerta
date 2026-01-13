<?php
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idRepresentante = $_POST['id_representante'];
    $statusBrasilCard = isset($_POST['status_brasil_card']) ? 1 : 0;
    $comPercBCard = $_POST['com_perc_bcard'] ?? '';
    $statusSouFacil = isset($_POST['status_soufacil']) ? 1 : 0;
    $comPercSouFacil = $_POST['com_perc_soufacil'] ?? '';
    $statusPagBank = isset($_POST['status_pagbank']) ? 1 : 0;
    $statusAdesao = isset($_POST['status_adesao']) ? 1 : 0;

    try {
        // Verificar se já existe uma configuração para o representante
        $sqlCheck = "SELECT COUNT(*) FROM representante_config WHERE id_representante = ?";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bind_param('i', $idRepresentante);
        $stmtCheck->execute();
        $stmtCheck->bind_result($exists);
        $stmtCheck->fetch();
        $stmtCheck->close();

        if ($exists > 0) {
            // Atualizar configuração existente
            $sqlUpdate = "
                UPDATE representante_config 
                SET 
                    status_brasil_card = ?, 
                    com_perc_bcard = ?, 
                    status_soufacil = ?, 
                    com_perc_soufacil = ?, 
                    status_pagbank = ?, 
                    status_adesao = ?
                WHERE id_representante = ?
            ";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param(
                'sssissi',
                $statusBrasilCard,
                $comPercBCard,
                $statusSouFacil,
                $comPercSouFacil,
                $statusPagBank,
                $statusAdesao,
                $idRepresentante
            );

            if ($stmtUpdate->execute()) {
                echo json_encode(['success' => true, 'message' => 'Configuração atualizada com sucesso.']);
            } else {
                throw new Exception('Erro ao atualizar a configuração: ' . $stmtUpdate->error);
            }
        } else {
            // Inserir nova configuração
            $sqlInsert = "
                INSERT INTO representante_config (
                    id_representante, status_brasil_card, com_perc_bcard, status_soufacil, com_perc_soufacil, status_pagbank, status_adesao
                ) VALUES (?, ?, ?, ?, ?, ?, ?)
            ";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bind_param(
                'sssissi',
                $idRepresentante,
                $statusBrasilCard,
                $comPercBCard,
                $statusSouFacil,
                $comPercSouFacil,
                $statusPagBank,
                $statusAdesao
            );

            if ($stmtInsert->execute()) {
                echo json_encode(['success' => true, 'message' => 'Nova configuração criada com sucesso.']);
            } else {
                throw new Exception('Erro ao criar a configuração: ' . $stmtInsert->error);
            }
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
