# Testes de Regressão Visual com BackstopJS

Este documento explica como usar a suíte de testes de regressão visual implementada no Laravel CRM usando BackstopJS.

## 📋 Índice

- [Visão Geral](#visão-geral)
- [Configuração](#configuração)
- [Scripts Disponíveis](#scripts-disponíveis)
- [Como Usar](#como-usar)
- [Interpretação dos Relatórios](#interpretação-dos-relatórios)
- [GitHub Actions](#github-actions)
- [Troubleshooting](#troubleshooting)

## 🎯 Visão Geral

Os testes de regressão visual automatizam a detecção de mudanças visuais não intencionais no frontend da aplicação. Utilizamos o BackstopJS para capturar screenshots de páginas específicas e compará-las com imagens de referência (baseline).

### Benefícios:
- **Detecção automática** de regressões visuais
- **Testes responsivos** em múltiplos viewports
- **Integração com CI/CD** via GitHub Actions
- **Relatórios visuais** detalhados das diferenças
- **Aprovação fácil** de mudanças intencionais

### Páginas Testadas:
- **Login Page**: Página de autenticação do admin
- **Agenda**: Interface do calendário FullCalendar
- **WhatsApp Web**: Interface de chat (quando disponível)




## ⚙️ Configuração

### Pré-requisitos

- Node.js 18+ instalado
- Laravel CRM rodando localmente na porta 8000
- Dependências npm instaladas

### Instalação

As dependências já estão configuradas no `package.json`:

```bash
npm install
```

### Estrutura de Arquivos

```
laravel-crm/
├── backstop.json                     # Configuração principal do BackstopJS
├── backstop_data/
│   ├── bitmaps_reference/            # Imagens de referência (baseline)
│   ├── bitmaps_test/                 # Screenshots dos testes
│   ├── html_report/                  # Relatórios HTML gerados
│   ├── ci_report/                    # Relatórios para CI
│   └── engine_scripts/
│       └── puppet/
│           ├── onBefore.js           # Script executado antes de cada teste
│           ├── onReady.js            # Script executado quando página está pronta
│           └── package.json          # Configuração CommonJS
├── docs/
│   └── visual-regression.md          # Esta documentação
└── .github/workflows/
    └── visual-regression.yml         # Pipeline GitHub Actions
```

### Configuração do BackstopJS

O arquivo `backstop.json` contém:

- **Viewports**: Phone (375x667), Tablet (768x1024), Desktop (1280x800)
- **Cenários**: Páginas específicas a serem testadas
- **Scripts**: Automação de login e preparação de páginas
- **Configurações**: Engine Puppeteer com argumentos otimizados


## 🚀 Scripts Disponíveis

### Scripts NPM

```bash
# Gerar imagens de referência (baseline)
npm run visual:ref

# Executar testes visuais
npm run visual:test

# Aprovar mudanças (atualizar baseline)
npm run visual:approve

# Abrir relatório HTML no navegador
npm run visual:report

# Limpar arquivos de teste
npm run visual:clean
```

### Comandos BackstopJS Diretos

```bash
# Gerar referência
npx backstop reference

# Executar testes
npx backstop test

# Aprovar mudanças
npx backstop approve

# Abrir relatório
npx backstop openReport
```

## 📖 Como Usar

### 1. Primeira Execução (Gerar Baseline)

Antes de executar os testes pela primeira vez, você precisa gerar as imagens de referência:

```bash
# Certifique-se que o Laravel está rodando
php artisan serve --host=0.0.0.0 --port=8000

# Em outro terminal, gere a baseline
npm run visual:ref
```

Este comando irá:
- Navegar para cada página configurada
- Fazer login automaticamente quando necessário
- Capturar screenshots em todos os viewports
- Salvar as imagens em `backstop_data/bitmaps_reference/`

### 2. Executar Testes

Após fazer mudanças no frontend, execute os testes:

```bash
npm run visual:test
```

Este comando irá:
- Capturar novos screenshots das páginas
- Comparar com as imagens de referência
- Gerar relatório de diferenças
- Abrir automaticamente o relatório HTML

### 3. Analisar Resultados

- **✅ Todos passaram**: Nenhuma regressão visual detectada
- **❌ Falhas detectadas**: Mudanças visuais encontradas

### 4. Aprovar Mudanças (se intencionais)

Se as mudanças são intencionais, atualize a baseline:

```bash
npm run visual:approve
```

Isso substitui as imagens de referência pelas novas capturas.


## 📊 Interpretação dos Relatórios

### Relatório HTML

O relatório principal está em `backstop_data/html_report/index.html` e contém:

#### Status dos Testes
- **✅ PASS**: Nenhuma diferença detectada
- **❌ FAIL**: Diferenças visuais encontradas

#### Visualização das Diferenças
Para cada teste que falhou, você verá:

1. **Reference**: Imagem de referência (baseline)
2. **Test**: Nova captura de tela
3. **Diff**: Diferenças destacadas em rosa/magenta

#### Métricas
- **Mismatch**: Percentual de diferença
- **Threshold**: Limite configurado (0.1% por padrão)

### Tipos de Diferenças Comuns

#### 1. Mudanças Intencionais ✅
- Novos elementos de UI
- Mudanças de layout aprovadas
- Atualizações de design

**Ação**: Aprovar com `npm run visual:approve`

#### 2. Regressões Não Intencionais ❌
- Elementos desalinhados
- Cores alteradas acidentalmente
- Quebras de layout

**Ação**: Corrigir o código e testar novamente

#### 3. Falsos Positivos ⚠️
- Timestamps dinâmicos
- Conteúdo que muda (já filtrado nos scripts)
- Diferenças de renderização do navegador

**Ação**: Ajustar configuração ou scripts se necessário

### Exemplo de Análise

```
❌ Agenda - Calendar View (Desktop)
Mismatch: 2.5% (Threshold: 0.1%)

Diferenças detectadas:
- Botão "Novo Evento" mudou de posição
- Cor do cabeçalho alterada de #3498db para #2980b9
```

**Decisão**: Se as mudanças são intencionais, aprovar. Se não, investigar e corrigir.


## 🔄 GitHub Actions

### Workflow Automático

O pipeline `.github/workflows/visual-regression.yml` executa automaticamente em:

- **Pull Requests** para `develop` e `main`
- **Mudanças** em arquivos de frontend
- **Execução manual** via workflow dispatch

### Processo do Pipeline

1. **Setup**: Instala dependências e configura ambiente
2. **Build**: Compila assets do frontend
3. **Database**: Configura banco de dados de teste
4. **Server**: Inicia Laravel na porta 8000
5. **Tests**: Executa testes visuais
6. **Report**: Gera e faz upload dos relatórios

### Resultados

#### ✅ Sucesso
- Todos os testes passaram
- Nenhuma regressão visual detectada
- PR pode ser aprovado

#### ❌ Falha
- Diferenças visuais encontradas
- Artifacts com relatórios disponíveis
- Comentário automático no PR com instruções

### Atualizando Baseline via CI

Para atualizar a baseline através do GitHub Actions:

1. Vá para **Actions** no repositório
2. Selecione **Visual Regression Tests**
3. Clique em **Run workflow**
4. Marque **Update baseline** como `true`
5. Execute o workflow

Isso irá:
- Gerar novas imagens de referência
- Fazer upload como artifact
- Disponibilizar para próximos testes

### Artifacts Gerados

Cada execução gera artifacts com:
- **visual-test-results-{run_number}**: Relatórios completos
- **visual-baseline**: Imagens de referência (quando atualizada)

### Comentários Automáticos no PR

O bot irá comentar automaticamente:

```markdown
## ✅ Visual Regression Tests Passed

All visual tests passed successfully! No regressions detected.

**Test Results**: [Download Artifact](link)
```

ou

```markdown
## ❌ Visual Regression Tests Failed

Visual changes detected. Please review the differences.

### 📋 Review Steps:
1. Download the visual-test-results artifact
2. Open backstop_data/html_report/index.html
3. If changes are intentional, update baseline

**Test Results**: [Download Artifact](link)
```


## 🔧 Troubleshooting

### Problemas Comuns

#### 1. Servidor Laravel não está rodando
```
Error: connect ECONNREFUSED 127.0.0.1:8000
```

**Solução**:
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

#### 2. Erro de autenticação
```
Error: Node is either not clickable or not an Element
```

**Causa**: Credenciais de login incorretas ou página de login mudou

**Solução**: Verificar credenciais em `backstop_data/engine_scripts/puppet/onBefore.js`

#### 3. Timeout nos testes
```
Error: waiting for selector "#calendar" failed: timeout 5000ms exceeded
```

**Causa**: Página demora para carregar ou elemento não existe

**Solução**: 
- Aumentar timeout no script
- Verificar se elemento existe na página
- Verificar se JavaScript está carregando corretamente

#### 4. Muitos falsos positivos
```
Mismatch: 15% (Threshold: 0.1%)
```

**Causa**: Elementos dinâmicos não filtrados

**Solução**: Adicionar seletores ao `hideSelectors` ou `removeSelectors` no `backstop.json`

#### 5. Erro de permissões
```
Error: EACCES: permission denied
```

**Solução**:
```bash
sudo chown -R $USER:$USER backstop_data/
chmod -R 755 backstop_data/
```

### Configurações Avançadas

#### Ajustar Threshold de Diferença

No `backstop.json`, para cada cenário:
```json
{
  "misMatchThreshold": 0.5,  // Aumentar para 0.5%
  "requireSameDimensions": false  // Permitir dimensões diferentes
}
```

#### Adicionar Novos Cenários

```json
{
  "label": "Nova Página",
  "url": "http://localhost:8000/admin/nova-pagina",
  "selectors": ["document"],
  "misMatchThreshold": 0.1
}
```

#### Filtrar Elementos Dinâmicos

```json
{
  "hideSelectors": [
    ".timestamp",
    "[data-dynamic]",
    ".loading-spinner"
  ],
  "removeSelectors": [
    ".phpdebugbar",
    ".notification-toast"
  ]
}
```

### Logs e Debug

#### Habilitar Debug
No `backstop.json`:
```json
{
  "debug": true,
  "debugWindow": true
}
```

#### Verificar Logs
```bash
# Logs detalhados
npx backstop test --debug

# Logs do Puppeteer
DEBUG=puppeteer:* npx backstop test
```

### Performance

#### Otimizar Execução
- Reduzir número de viewports se não necessário
- Usar `asyncCaptureLimit` menor para ambientes com pouca memória
- Configurar `engineOptions` para melhor performance

#### Exemplo de Configuração Otimizada
```json
{
  "asyncCaptureLimit": 3,
  "asyncCompareLimit": 25,
  "engineOptions": {
    "args": [
      "--no-sandbox",
      "--disable-setuid-sandbox",
      "--disable-dev-shm-usage",
      "--disable-gpu",
      "--disable-web-security"
    ]
  }
}
```

---

## 📞 Suporte

Para dúvidas ou problemas:

1. Consulte esta documentação
2. Verifique os logs de execução
3. Analise o relatório HTML gerado
4. Consulte a [documentação oficial do BackstopJS](https://github.com/garris/BackstopJS)

---

**Última atualização**: Julho 2025  
**Versão BackstopJS**: 6.3.25

