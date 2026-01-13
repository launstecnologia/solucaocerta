<?php
require_once '../config/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $idRepresentante = $_GET['id_representante'] ?? null;

    if ($idRepresentante) {
        try {
            // Verificar se já existe uma configuração
            $sql = "SELECT * FROM representante_config WHERE id_representante = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $idRepresentante);
            $stmt->execute();
            $result = $stmt->get_result();
            $config = $result->fetch_assoc();

            if ($config) {
                // Retorna a configuração encontrada
                echo json_encode(['success' => true, 'config' => $config]);
            } else {
                // Criar uma nova configuração padrão
                $sqlInsert = "
                    INSERT INTO representante_config (
                        id_representante, status_brasil_card, com_perc_bcard, status_soufacil, com_perc_soufacil, status_pagbank, status_adesao
                    ) VALUES (?, 0, 0.00, 0, 0.00, 0, 0)
                ";
                $stmtInsert = $conn->prepare($sqlInsert);
                $stmtInsert->bind_param('i', $idRepresentante);

                if ($stmtInsert->execute()) {
                    // Retornar a nova configuração criada
                    $sqlFetch = "SELECT * FROM representante_config WHERE id_representante = ?";
                    $stmtFetch = $conn->prepare($sqlFetch);
                    $stmtFetch->bind_param('i', $idRepresentante);
                    $stmtFetch->execute();
                    $newResult = $stmtFetch->get_result();
                    $newConfig = $newResult->fetch_assoc();

                    echo json_encode(['success' => true, 'config' => $newConfig]);
                } else {
                    throw new Exception('Erro ao criar nova configuração: ' . $stmtInsert->error);
                }
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'ID do representante não fornecido.']);
    }
}
