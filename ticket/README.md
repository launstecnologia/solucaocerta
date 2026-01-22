# Sistema de Tickets - Melhorias Implementadas

## Funcionalidades Adicionadas

### 1. Filtros Avançados para Seleção de Cliente
- Busca por CNPJ, CPF, Nome Fantasia, Razão Social
- Filtro por Cidade, Representante, PDV e Produto
- Busca em tempo real com resultados dinâmicos
- Seleção fácil do cliente desejado

### 2. Upload de Anexos
- Suporte a múltiplos arquivos por ticket
- Tipos permitidos: imagens, PDF, documentos Word/Excel, arquivos de texto
- Limite de 10MB por arquivo
- Preview dos arquivos antes do envio
- Download dos anexos na visualização do ticket

### 3. Data de Retorno
- Campo opcional para agendar data/hora de retorno
- Notificações automáticas quando a data se aproxima
- Alertas visuais na listagem e visualização de tickets
- Cores diferentes conforme urgência:
  - 🔴 Vermelho: Retorno em menos de 1 hora ou já passou
  - 🟡 Amarelo: Retorno em menos de 24 horas
  - Normal: Retorno em mais de 24 horas

### 4. Sistema de Notificações
- Notificações automáticas quando a data de retorno se aproxima
- Painel de notificações na listagem de tickets
- Badge com contador de notificações não lidas
- Atualização automática a cada 30 segundos

## Configuração

### 1. Executar Migration
Execute o arquivo SQL de migration para criar as tabelas e campos necessários:
```sql
database/migration/004_20260114_improve_tickets_system.sql
```

### 2. Criar Diretório de Uploads
Crie o diretório para armazenar os anexos:
```bash
mkdir -p uploads/tickets
chmod 755 uploads/tickets
```

### 3. Configurar Cron Job para Notificações
Para que as notificações funcionem automaticamente, configure um cron job que execute o script de verificação a cada 5 minutos:

**Linux/Unix:**
```bash
*/5 * * * * php /caminho/completo/para/ticket/verificar_notificacoes.php
```

**Windows (Task Scheduler):**
- Criar uma tarefa agendada que execute:
```
php.exe C:\caminho\completo\para\ticket\verificar_notificacoes.php
```
- Configurar para executar a cada 5 minutos

**Ou via navegador (alternativa):**
Você pode criar um endpoint que chame o script e configurar um serviço externo (como UptimeRobot) para acessá-lo a cada 5 minutos.

## Estrutura de Arquivos

```
ticket/
├── create_ticket.php          # Formulário de criação com filtros avançados
├── save_ticket.php            # Processamento e salvamento do ticket
├── index.php                  # Listagem de tickets com notificações
├── view_ticket.php            # Visualização detalhada com anexos e alertas
├── edit_ticket.php            # Edição de ticket (inclui data_retorno)
├── update_ticket.php          # Atualização do ticket
├── reply_ticket.php           # Responder ticket
├── save_reply.php             # Salvar resposta
├── verificar_notificacoes.php # Script de verificação (cron)
├── ajax_buscar_cliente.php    # API para busca de clientes
├── ajax_contar_notificacoes.php # API para contar notificações
└── ajax_marcar_notificacao_lida.php # API para marcar notificação como lida
```

## Tabelas do Banco de Dados

### tickets (atualizada)
- `data_retorno` (DATETIME): Data e hora agendada para retorno
- `notificado` (TINYINT): Flag indicando se já foi notificado

### ticket_anexos (nova)
- Armazena informações dos arquivos anexados aos tickets

### ticket_notificacoes (nova)
- Armazena notificações de retorno e outras notificações do sistema

## Uso

### Criar Ticket
1. Acesse "Criar Ticket"
2. Use os filtros para buscar o cliente desejado
3. Clique no cliente nos resultados para selecioná-lo
4. Preencha título, descrição e status
5. (Opcional) Defina data/hora de retorno
6. (Opcional) Anexe arquivos
7. Clique em "Salvar Ticket"

### Visualizar Notificações
- O badge de notificações aparece no topo da listagem quando há notificações não lidas
- Clique no botão "🔔 Notificações" para abrir o painel
- Clique em uma notificação para ir direto ao ticket

### Alertas de Retorno
- Tickets com retorno próximo aparecem destacados na listagem
- Na visualização do ticket, há alertas visuais conforme a urgência
- As cores indicam o nível de urgência

## Observações

- Os anexos são armazenados em `uploads/tickets/{id_ticket}/`
- O sistema verifica notificações a cada 5 minutos (via cron)
- Notificações são criadas automaticamente quando:
  - Um ticket é criado com data_retorno
  - A data_retorno de um ticket é atualizada
  - O script de verificação detecta retornos próximos








