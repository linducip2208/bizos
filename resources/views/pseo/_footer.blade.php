<footer class="bg-slate-900 text-slate-400 mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
            <div>
                <h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Produk</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/docs') }}" class="hover:text-white transition-colors no-underline">Dokumentasi</a></li>
                    <li><a href="{{ url('/best-hrm-software') }}" class="hover:text-white transition-colors no-underline">Fitur HRM</a></li>
                    <li><a href="{{ url('/best-accounting-software-indonesia') }}" class="hover:text-white transition-colors no-underline">Fitur Akuntansi</a></li>
                    <li><a href="{{ url('/compare/bizos-vs-spreadsheet') }}" class="hover:text-white transition-colors no-underline">vs Spreadsheet</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Akses</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/admin/login') }}" class="hover:text-white transition-colors no-underline">Admin Login</a></li>
                    <li><a href="{{ url('/sitemap.xml') }}" class="hover:text-white transition-colors no-underline">Sitemap</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Perusahaan</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ url('/') }}" class="hover:text-white transition-colors no-underline">Beranda</a></li>
                    <li><a href="#" class="hover:text-white transition-colors no-underline">Kontak</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-4 text-sm uppercase tracking-wider">Legal</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="hover:text-white transition-colors no-underline">Privacy Policy</a></li>
                    <li><a href="#" class="hover:text-white transition-colors no-underline">Terms of Service</a></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-slate-800 mt-10 pt-6 text-center text-xs text-slate-500">
            &copy; {{ date('Y') }} BizOS — Business Operating System. Seluruh hak cipta dilindungi.
        </div>
    </div>
</footer>
