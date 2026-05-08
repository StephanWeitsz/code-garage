@extends('layouts.app', ['title' => 'Deployment Tools'])

@section('content')
    <section class="stack">
        <div class="section-heading">
            <div>
                <p class="eyebrow">Operations</p>
                <h1>Deployment Tools</h1>
                <p class="muted">Admin-only maintenance actions for hosting environments with no console access.</p>
            </div>
        </div>

        <article class="panel stack">
            <h2>Environment Snapshot</h2>
            <p><strong>APP_ENV:</strong> {{ $app['env'] }}</p>
            <p><strong>APP_URL:</strong> {{ $app['url'] ?: 'Not set' }}</p>
            <p><strong>ASSET_URL:</strong> {{ $app['asset_url'] ?: 'Not set' }}</p>
            <p><strong>APP_DEBUG:</strong> {{ $app['debug'] ? 'true' : 'false' }}</p>
        </article>

        <article class="panel stack">
            <h2>Checks</h2>
            <p>
                <strong>Storage link status:</strong>
                @if ($checks['storage_status'] === 'linked-symlink')
                    Linked (symlink)
                @elseif ($checks['storage_status'] === 'usable-directory')
                    Usable (directory mode)
                @else
                    Not linked
                @endif
            </p>
            <p><strong>Storage link healthy:</strong> {{ $checks['storage_link'] ? 'Yes' : 'No' }}</p>
            <p><strong>`public/storage` exists:</strong> {{ $checks['public_storage_exists'] ? 'Yes' : 'No' }}</p>
            <p><strong>Vite build manifest exists:</strong> {{ $checks['vite_manifest'] ? 'Yes' : 'No' }}</p>
            <p><strong>Filament vendor assets exist:</strong> {{ $checks['filament_vendor_assets'] ? 'Yes' : 'No' }}</p>
            <p><strong>`storage/logs/laravel.log` exists:</strong> {{ $checks['log_exists'] ? 'Yes' : 'No' }}</p>
        </article>

        <article class="panel stack">
            <h2>Quick Actions</h2>
            <div class="hero-actions">
                <form method="POST" action="{{ route('deployment-tools.run') }}">
                    @csrf
                    <input type="hidden" name="action" value="storage-link">
                    <button type="submit" class="button button-primary">Run storage:link</button>
                </form>

                <form method="POST" action="{{ route('deployment-tools.run') }}">
                    @csrf
                    <input type="hidden" name="action" value="clear-caches">
                    <button type="submit" class="button button-secondary">Run optimize:clear</button>
                </form>

                <form method="POST" action="{{ route('deployment-tools.run') }}">
                    @csrf
                    <input type="hidden" name="action" value="filament-upgrade">
                    <button type="submit" class="button button-secondary">Run filament:upgrade</button>
                </form>
            </div>
            <p class="muted">
                If admin/lecturer pages are blank on hosting, run <strong>optimize:clear</strong> and <strong>filament:upgrade</strong>, then refresh.
            </p>
        </article>

        @php($deploymentResult = session('deployment_result'))
        @if ($deploymentResult)
            <article class="panel stack">
                <h2>Last Command Output</h2>
                <p><strong>Result:</strong> {{ $deploymentResult['ok'] ? 'Success' : 'Failed' }}</p>
                <pre class="deployment-output">{{ $deploymentResult['output'] }}</pre>
            </article>
        @endif

        <article class="panel stack">
            <h2>Paths</h2>
            <p><strong>Public storage path:</strong> {{ $paths['public_storage'] }}</p>
            <p><strong>Storage public path:</strong> {{ $paths['storage_public'] }}</p>
            <p><strong>Build manifest:</strong> {{ $paths['build_manifest'] }}</p>
            <p><strong>Log file:</strong> {{ $paths['log'] }}</p>
        </article>
    </section>
@endsection
