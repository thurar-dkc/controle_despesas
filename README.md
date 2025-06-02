RELATÓRIO DA APLICAÇÃO: CONTROLE DE DESPESAS PESSOAIS SIMPLIFICADO

Data do Relatório: 02/06/2025

1. INTRODUÇÃO
   Este relatório descreve a aplicação web "Controle de Despesas Pessoais Simplificado", desenvolvida para permitir o registro e acompanhamento de despesas pessoais de forma organizada. A aplicação foi construída utilizando tecnologias web padrão e visa oferecer uma interface simples e funcional para o usuário final.

2. OBJETIVO DA APLICAÇÃO
   O principal objetivo da aplicação é fornecer uma ferramenta para que os usuários possam:
   - Cadastrar diferentes categorias de despesas (ex: Alimentação, Transporte).
   - Registrar suas despesas diárias, associando-as a uma categoria e especificando valor, data e descrição.
   - Visualizar relatórios de despesas, permitindo uma análise simplificada dos gastos.

3. TECNOLOGIAS UTILIZADAS
   - Linguagem de Programação Backend: PHP
   - Servidor Web e Ambiente de Desenvolvimento: XAMPP (incluindo Apache)
   - Banco de Dados: MySQL
   - Linguagens Frontend: HTML, CSS (para estilização básica)

4. ESTRUTURA DO BANCO DE DADOS
   O banco de dados, nomeado `controle_despesas_db`, é composto por duas tabelas principais:

   4.1. Tabela `categorias`:
      - `id` (INT, AUTO_INCREMENT, PRIMARY KEY): Identificador único da categoria.
      - `nome` (VARCHAR(100), NOT NULL, UNIQUE): Nome da categoria.
      - `descricao` (TEXT, NULL): Descrição opcional da categoria.
      - `data_criacao` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP): Data de criação do registro.

   4.2. Tabela `despesas`:
      - `id` (INT, AUTO_INCREMENT, PRIMARY KEY): Identificador único da despesa.
      - `categoria_id` (INT, NOT NULL, FOREIGN KEY): Referência à `id` da tabela `categorias`.
      - `descricao` (VARCHAR(255), NOT NULL): Descrição da despesa.
      - `valor` (DECIMAL(10, 2), NOT NULL): Valor monetário da despesa.
      - `data_despesa` (DATE, NOT NULL): Data em que a despesa ocorreu.
      - `observacoes` (TEXT, NULL): Observações adicionais sobre a despesa.
      - `data_criacao` (TIMESTAMP, DEFAULT CURRENT_TIMESTAMP): Data de criação do registro.

5. ARQUITETURA E ARQUIVOS DA APLICAÇÃO
   A aplicação foi estruturada nos seguintes arquivos principais, localizados na pasta `controle_despesas` dentro do diretório `htdocs` do XAMPP:

   - `database.sql`: Script SQL para criação das tabelas e inserção de dados iniciais (opcional).
   - `db.php`: Script PHP para configuração e estabelecimento da conexão com o banco de dados MySQL.
   - `header.php`: Contém o cabeçalho HTML, incluindo a estrutura básica da página, links para CSS e a barra de navegação principal.
   - `footer.php`: Contém o rodapé HTML e o fechamento da conexão com o banco de dados.
   - `style.css`: Folha de estilos CSS para a aparência visual básica da aplicação.
   - `index.php`: Ponto de entrada principal da aplicação. Atua como um roteador simples, carregando o conteúdo das páginas (`categorias.php`, `despesas.php`, `relatorios.php`) com base em parâmetros da URL.
   - `categorias.php`: Responsável pela interface e lógica de Gerenciamento de Categorias (Adicionar, Listar, Editar e Deletar categorias). Inclui formulários e listagem de dados.
   - `despesas.php`: Responsável pela interface e lógica de Gerenciamento de Despesas (Adicionar, Listar, Editar e Deletar despesas). Inclui formulários com seleção de categoria e listagem de dados.
   - `relatorios.php`: Apresenta um relatório das despesas registradas, com funcionalidades de filtro por mês/ano e por categoria. Exibe o total das despesas filtradas.

6. FUNCIONALIDADES IMPLEMENTADAS

   6.1. Gerenciamento de Categorias:
      - Adição de novas categorias com nome e descrição opcional.
      - Listagem de todas as categorias existentes.
      - Edição dos dados de categorias existentes.
      - Exclusão de categorias (com verificação para impedir exclusão de categorias em uso por despesas).

   6.2. Gerenciamento de Despesas:
      - Adição de novas despesas, incluindo seleção de categoria, descrição, valor, data e observações opcionais.
      - Listagem de todas as despesas registradas.
      - Edição dos dados de despesas existentes.
      - Exclusão de despesas.

   6.3. Relatórios:
      - Exibição de uma lista de despesas.
      - Filtro de despesas por mês/ano.
      - Filtro de despesas por categoria.
      - Exibição do valor total das despesas que correspondem aos filtros aplicados.

   6.4. Interface e Usabilidade:
      - Navegação simplificada através de uma barra de navegação superior.
      - Formulários claros para entrada de dados.
      - Mensagens de feedback (sucesso, erro, aviso) para o usuário após as operações (usando sessões PHP).
      - Confirmação antes da exclusão de registros.

   6.5. Segurança:
      - Uso de `prepared statements` do MySQLi para interações com o banco de dados, prevenindo injeções de SQL.
      - Uso de `htmlspecialchars()` para sanitizar dados de saída e prevenir XSS básico.

7. INSTRUÇÕES DE EXECUÇÃO
   1. Instalar o XAMPP (ou ambiente similar com Apache, PHP e MySQL).
   2. Iniciar os serviços Apache e MySQL.
   3. Criar um banco de dados chamado `controle_despesas_db` no MySQL (ex: via phpMyAdmin).
   4. Executar o script `database.sql` no banco de dados criado para gerar as tabelas.
   5. Colocar todos os arquivos da aplicação (`db.php`, `index.php`, `header.php`, `footer.php`, `style.css`, `categorias.php`, `despesas.php`, `relatorios.php`) em uma pasta dentro do diretório `htdocs` do XAMPP (ex: `htdocs/controle_despesas/`).
   6. Acessar a aplicação pelo navegador através do URL: `http://localhost/controle_despesas/`.

8. PONTOS DE MELHORIA FUTURA
   - Implementação de autenticação de usuários para controle de acesso individual.
   - Validação de dados mais robusta no lado do cliente (JavaScript) e servidor.
   - Paginação para listas extensas de categorias e despesas.
   - Relatórios mais avançados, com gráficos e exportação de dados (ex: CSV, PDF).
   - Melhoria da interface do usuário (UI) e experiência do usuário (UX).
   - Criação de uma API para consumo dos dados por outras aplicações.
   - Testes unitários e de integração.
   - Tratamento de erros mais sofisticado e logging.

9. CONCLUSÃO
   A aplicação "Controle de Despesas Pessoais Simplificado" desenvolvida atende aos requisitos básicos propostos, fornecendo uma plataforma funcional para o gerenciamento de finanças pessoais. A estrutura modular e o uso de boas práticas de programação (como `prepared statements`) estabelecem uma base sólida para futuras expansões e melhorias.
