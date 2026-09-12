<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow, noarchive">
    <title>Instalação VPS — documentação interna</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f4f1ea;
            --paper: #fffdf8;
            --ink: #1c1917;
            --muted: #57534e;
            --line: #e7e0d4;
            --accent: #1d4ed8;
            --code-bg: #1c1917;
            --code-fg: #fafaf9;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", system-ui, sans-serif;
            background: var(--bg);
            color: var(--ink);
            line-height: 1.6;
        }
        .wrap {
            max-width: 880px;
            margin: 0 auto;
            padding: 2rem 1.25rem 4rem;
        }
        .banner {
            font-size: 0.8rem;
            letter-spacing: .04em;
            text-transform: uppercase;
            color: #9a3412;
            background: #ffedd5;
            border: 1px solid #fdba74;
            border-radius: 8px;
            padding: .6rem .9rem;
            margin-bottom: 1.5rem;
        }
        article {
            background: var(--paper);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 2rem 1.75rem;
        }
        article h1 { font-size: 1.85rem; margin-top: 0; }
        article h2 {
            font-size: 1.35rem;
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid var(--line);
        }
        article h3 { font-size: 1.1rem; margin-top: 1.5rem; }
        article p, article li { color: var(--ink); }
        article a { color: var(--accent); }
        article table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.92rem;
            margin: 1rem 0;
        }
        article th, article td {
            border: 1px solid var(--line);
            padding: .45rem .6rem;
            text-align: left;
            vertical-align: top;
        }
        article th { background: #f5f0e6; }
        article pre {
            background: var(--code-bg);
            color: var(--code-fg);
            padding: 1rem;
            border-radius: 8px;
            overflow-x: auto;
            font-size: 0.85rem;
        }
        article code {
            font-family: ui-monospace, "Cascadia Code", Consolas, monospace;
            font-size: 0.88em;
        }
        article :not(pre) > code {
            background: #efe8dc;
            padding: .1rem .35rem;
            border-radius: 4px;
        }
        article blockquote {
            margin: 1rem 0;
            padding: .4rem 1rem;
            border-left: 4px solid #c2410c;
            background: #fff7ed;
            color: var(--muted);
        }
        article hr { border: 0; border-top: 1px solid var(--line); }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="banner">Documentação interna — não indexar · acesso somente com o link secreto do .env</div>
        <article>
            {!! $content !!}
        </article>
    </div>
</body>
</html>
