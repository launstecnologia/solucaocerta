-- Adiciona colunas Adesão e Valor na tabela parcelex (ações do produto no detalhe do cliente).
-- Execute este script no banco se ainda não existirem as colunas.

ALTER TABLE parcelex
  ADD COLUMN IF NOT EXISTS adesao VARCHAR(10) DEFAULT NULL COMMENT 'Sim ou Não',
  ADD COLUMN IF NOT EXISTS valor VARCHAR(50) DEFAULT NULL COMMENT 'Valor da adesão';

-- MySQL 5.x não suporta IF NOT EXISTS em ADD COLUMN; use apenas:
-- ALTER TABLE parcelex ADD COLUMN adesao VARCHAR(10) DEFAULT NULL;
-- ALTER TABLE parcelex ADD COLUMN valor VARCHAR(50) DEFAULT NULL;
