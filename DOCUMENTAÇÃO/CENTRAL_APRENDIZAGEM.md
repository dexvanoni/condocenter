# Central de Aprendizagem — vídeos

Os tutoriais críticos do síndico exibem player de vídeo quando o arquivo existir nesta pasta:

`public/videos/learning/`

## Como publicar um vídeo

1. Grave a tela do fluxo no SindCON (sugestão: 1080p, sem dados sensíveis reais).
2. Exporte em **MP4** (H.264).
3. Salve com o **mesmo nome** referenciado no catálogo (`app/Support/Learning/LearningCatalog.php`, campo `video`).
4. Exemplos atuais:

| Arquivo | Tutorial |
|---------|----------|
| `financeiro-visao-geral.mp4` | Visão geral do Financeiro |
| `contas-bancarias.mp4` | Contas e regras de destino |
| `taxas-cobrancas.mp4` | Taxas e cobranças |
| `caixa-condominio.mp4` | Caixa do condomínio |
| `dashboard-categorias.mp4` | Dashboard: custos e previsões por categoria |
| `conciliacao-bancaria.mp4` | Conciliação CSV/OFX |
| `fechamento-mensal.mp4` | Fechamento mensal |
| `unidades-moradores.mp4` | Unidades e moradores |

5. Faça deploy do arquivo junto com o código. O player aparece sozinho; não precisa migração.

## Alternativa

No catálogo, use `video_url` com link de YouTube/Vimeo (embed) se preferir hospedar fora do servidor.

## Acesso no sistema

- Menu lateral: **Aprenda**
- Perfil do usuário: **Central de Aprendizagem**
- URL: `/aprender`
