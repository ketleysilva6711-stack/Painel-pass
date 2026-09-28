<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $app->name }} - The Vault Store</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: -apple-system, Roboto, "Segoe UI", Arial, sans-serif; background: #f8f9fa; color: #202124; }
  .topbar { background: #fff; border-bottom: 1px solid #e0e0e0; padding: 12px 24px; font-weight: 600; color: #1a73e8; }
  .container { max-width: 960px; margin: 24px auto; padding: 0 16px; }
  .card { background: #fff; border-radius: 12px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,.12); }
  .header { display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap; }
  .icon { width: 96px; height: 96px; border-radius: 20px; object-fit: cover; background: #e8eaed; }
  .icon-fallback { width: 96px; height: 96px; border-radius: 20px; background: #1a73e8; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 40px; font-weight: 700; }
  .title { font-size: 24px; font-weight: 600; }
  .dev { color: #1a73e8; font-size: 14px; margin-top: 4px; }
  .meta { color: #5f6368; font-size: 13px; margin-top: 8px; }
  .stats { display: flex; gap: 32px; margin: 20px 0; flex-wrap: wrap; }
  .stat b { display: block; font-size: 15px; }
  .stat span { color: #5f6368; font-size: 12px; }
  .actions { display: flex; gap: 12px; margin: 16px 0; flex-wrap: wrap; }
  .btn { display: inline-block; background: #1a73e8; color: #fff; padding: 12px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; }
  .btn.secondary { background: #e8f0fe; color: #1a73e8; }
  .qr { margin-top: 16px; display: flex; gap: 16px; align-items: center; flex-wrap: wrap; }
  .qr img { width: 140px; height: 140px; border: 1px solid #e0e0e0; border-radius: 8px; background: #fff; }
  .perms { margin-top: 20px; }
  .perms h3 { font-size: 16px; margin-bottom: 8px; }
  .perms li { font-size: 13px; color: #5f6368; margin-left: 18px; }
  .footer { text-align: center; color: #9aa0a6; font-size: 12px; margin: 24px 0; }
</style>
</head>
<body>
<div class="topbar">▶ The Vault Store</div>
<div class="container">
  <div class="card">
    <div class="header">
      @if($iconUrl)
        <img class="icon" src="{{ $iconUrl }}" alt="Ícone de {{ $app->name }}">
      @else
        <div class="icon-fallback">{{ mb_substr($app->name, 0, 1) }}</div>
      @endif
      <div>
        <div class="title">{{ $app->name }}</div>
        <div class="dev">{{ $app->developer ?? 'Google LLC' }}</div>
        <div class="meta">
          {{ $app->package_name ?? '' }}
          @if($app->version_name) · v{{ $app->version_name }}@endif
          @if($app->version_code) ({{ $app->version_code }})@endif
          · {{ $app->file_size ? round($app->file_size / 1024 / 1024, 1) . ' MB' : '45 MB' }}
        </div>
      </div>
    </div>

    <div class="stats">
      <div class="stat"><b>4.8 ★</b><span>125 mil avaliações</span></div>
      <div class="stat"><b>{{ number_format($app->downloads ?? 0, 0, ',', '.') }}</b><span>Downloads</span></div>
      <div class="stat"><b>@if($app->version_name)v{{ $app->version_name }}@else —@endif</b><span>Versão</span></div>
    </div>

    <div class="actions">
      @if($downloadUrl)
        <a class="btn" href="{{ $downloadUrl }}" download>Instalar / Baixar APK</a>
      @endif
      <a class="btn secondary" href="{{ $qrUrl }}" download>Baixar QR Code</a>
    </div>

    <div class="qr">
      <img src="{{ $qrUrl }}" alt="QR Code de instalação">
      <div class="meta">Escaneie para abrir esta página<br>e instalar rapidamente.</div>
    </div>

    @if(!empty($permissions))
    <div class="perms">
      <h3>Permissões do app</h3>
      <ul>
        @foreach(array_slice($permissions, 0, 30) as $p)
          <li>{{ $p }}</li>
        @endforeach
      </ul>
    </div>
    @endif
  </div>
  <div class="footer">Página gerada pelo The Vault · slug {{ $app->slug }}</div>
</div>
</body>
</html>
