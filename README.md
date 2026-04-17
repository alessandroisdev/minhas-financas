<br />
<div align="center">
  <img src="https://laravel.com/img/logomark.min.svg" alt="Logo" width="80" height="80">
  <h3 align="center">Minhas Finanças (Enterprise Edition)</h3>

  <p align="center">
    Um poderoso ecossistema de gestão corporativa aliando Finanças de Alta Performance a um Arquivamento Baseado em Visão Computacional (OCR)
    <br />
  </p>
</div>

## 🌐 Sobre o Projeto

**Minhas Finanças** foi evoluído de um tracker simples para uma arquitetura contábil **Tier-1**. Desenvolvido sob Laravel com ambientes em Docker puristas e Filas em Redis, o sistema não apenas cuida do fluxo de caixa inteligente e transações com recorrência vitalícia/automáticas, como possui um autêntico **Google Drive Integrado** nas entranhas. 

Todo arquivo depositado é submetido a agentes lógicos rodando nativamente no contêiner com motores OCR de Machine Learning para vasculhar os textos no interior das fotos e dos papéis.

### Stack Tecnológica & Infraestrutura

* **Kernel:** Laravel Eloquent & PHP FPM
* **Service Workers:** Redis Queue Containers (Agnósticos e assíncronos)
* **Arquitetura de Dados:** MySQL 8.0 suportando Queries `FULLTEXT MATCH-AGAINST` e Schemas Recursivos Parentais (Árvores N-finitas).
* **Environment:** Ultra Isolamento com Docker-Compose e Dockerfiles compilados unindo bibliotecas Linux robustas (`Tesseract`, `Poppler`).

## 🚀 Módulo Arquivológico e A.I (Cofre)

Uma quebra de paradigma na anexação de recibos e documentações fiscais:
- **Arrasto de Ramificações WebKit:** Arraste centenas de Diretórios Windows/Mac OS pesados contendo sub-pastas diretamente na tela do sistema usando recursos do `HTML5 DataTransfer`. O backend reconstruirá o galho de pastas automaticamente.
- **Leitura Neural Dinâmica (Tesseract OCR / Smalot PDF):** Uma "Queue" fantasma recebe seus PDFs e fotos subidas, destrincha cada byte das imagens identificando grafias e injetando as frases na sua tabela, tornando seu Cofre imune a extravios lógicos.
- **Dual-Search Adaptativo:** Pesquise milissegundamente via JavaScript Engine (Nomes e Tags visíveis) ou vire a chave no Front-End para evocar a varredura C++ no Back-End pelo que está criptografado e escrito fisicamente *escondido dentro do papel*.
- **Proteção S3-Like e Segredos:** Trava física contra vazamentos `public/`. Um arquivo no cofre possui uma blindagem que invoca um cadeado digital obrigando re-checagem do Token de Senha do usuário antes do servidor gerar os Bytestreams para Download.

## 📊 Dashboard Financeiro

- **Projeções Universais:** Abandono da limitante funcionalidade de parcelas engessadas e injeção do motor de Cálculos a Prazo e Transações Indeterminadas (Vitalícias/Mensalidades) em conformidade matemática.
- **Interface Analítica Inteligente:** Apresentações e Insights de finanças exibidas usando a imersiva suite de UI `Chart.js` via renderização Vanilla sem frames de peso, resultando em visualização Zero-Delay.

## 🛠 Como iniciar este Repositório (Setup de Contêineres)

Clone as matrizes estruturais, desça para a pasta primária de isolamento e convoque a inicialização do Motor Docker. Tudo já está compilado dentro do script:

```bash
# 1. Navegue para o Módulo de Contêineres
cd .docker

# 2. Inicialize o cluster com re-compilação bruta dos pacotes do SO e Workers
docker-compose up -d --build

# 3. Injete as migrações MySQL
docker exec app_minhas_financas php artisan migrate

# 4. Caso possua travas do ambiente host e precise atualizar as IAs 
docker exec app_minhas_financas composer update
```

## 🔥 Acesso e Portas

- **Core Web App:** `http://localhost:8084`
- **Container Database:** `Porta 3306` mapeada. Extinção do root isolado; Banco criado nativamente via Environments do Compose.
- **Filas de Processamento:** `queue_minhas_financas` lida ativamente em Background garantindo que nenhuma lentidão de Scanner de Nota Fiscal atinja a ponta do Usuário Final.

---
_Desenvolvido sob rígidos padrões corporativos e desenhado para escalar no nível de Big Data Fiscais._
