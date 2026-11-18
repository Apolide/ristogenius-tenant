    <footer class="tenant-public-form-footer">
        <div class="flex h-full w-full flex-col items-center justify-center gap-1 px-4 text-center text-xs sm:flex-row sm:justify-between sm:px-6 sm:text-left">
            <span>
                <strong>{{ $branding['name'] }}</strong>
                @if ($branding['address'])
                    <span aria-hidden="true"> · </span>{{ $branding['address'] }}
                @endif
            </span>
            <a href="https://ristogenius.com" target="_blank" rel="noopener noreferrer">by <strong>RistoGenius</strong></a>
        </div>
    </footer>
